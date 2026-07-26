#!/usr/bin/env python3
"""
Trendyol urun gorseli hazirlayici
- Duz/temiz arka planli urun fotograflarindan arka plani siler
- Beyaz zemine oturtur, 1200x1800 (2:3) dikey kadraja getirir
- HEIC, JPG, PNG, WEBP destekler

Kullanim:
    python3 trendyol_gorsel.py <girdi_klasoru> <cikti_klasoru>
"""
import sys, os, glob
import numpy as np
import cv2
from PIL import Image

try:
    import pillow_heif
    pillow_heif.register_heif_opener()
except ImportError:
    pass

# ---- Trendyol standartlari ----
OUT_W, OUT_H = 1200, 1800     # 2:3 dikey
FILL_RATIO   = 0.82           # urun kadrajin ~%82'sini kaplasin
BG_COLOR     = (255, 255, 255)

# ---- Segmentasyon ayarlari ----
COLOR_T   = 18    # LAB renk farki esigi (dusurursen daha cok yakalar, golgeyi de alir)
GRAD_T    = 22    # kenar (gradyan) esigi
MIN_AREA  = 0.0006  # goruntu alaninin bu kesrinden kucuk parcalar atilir
ALPHA_LO  = 15    # bu renk farkinin altindaki pikseller tamamen seffaf (fon)
ALPHA_HI  = 34    # bu farkin ustundekiler tamamen opak (urun)


def load_rgb(path):
    img = Image.open(path).convert("RGB")
    return np.array(img)


def border_bg_color(lab, band=40):
    """Kenar seritlerinden arka plan rengini tahmin et (medyan)."""
    h, w = lab.shape[:2]
    strips = [lab[:band], lab[-band:], lab[:, :band].reshape(-1, 3),
              lab[:, -band:].reshape(-1, 3)]
    px = np.vstack([s.reshape(-1, 3) for s in strips])
    return np.median(px, axis=0)


def autocrop_to_backdrop(rgb):
    """Kadrajdaki dagiik/koyu bolgeleri kirp, sadece duz fon alanini birak."""
    lab = cv2.cvtColor(rgb, cv2.COLOR_RGB2LAB).astype(np.float32)
    bg = border_bg_color(lab)
    d = lab - bg
    dist = np.sqrt(0.35 * d[..., 0] ** 2 + 2.0 * d[..., 1] ** 2 + 2.0 * d[..., 2] ** 2)
    bgness = (dist < COLOR_T).astype(np.float32)

    def span(profile, thr=0.35, run=25):
        """Merkezden disa dogru genisle; fon olmayan uzun bir seride dur."""
        ok = profile > thr
        n = len(ok)
        c = n // 2
        lo = 0
        bad = 0
        for i in range(c, -1, -1):
            bad = bad + 1 if not ok[i] else 0
            if bad >= run:
                lo = i + run
                break
        hi = n
        bad = 0
        for i in range(c, n):
            bad = bad + 1 if not ok[i] else 0
            if bad >= run:
                hi = i - run + 1
                break
        return lo, max(hi, lo + 1)

    x0, x1 = span(bgness.mean(axis=0))
    y0, y1 = span(bgness.mean(axis=1))
    if (x1 - x0) < rgb.shape[1] * 0.3 or (y1 - y0) < rgb.shape[0] * 0.3:
        return rgb
    return rgb[y0:y1, x0:x1]


def foreground_mask(rgb):
    h, w = rgb.shape[:2]
    lab = cv2.cvtColor(rgb, cv2.COLOR_RGB2LAB).astype(np.float32)

    # 1) Arka plan rengine gore renk farki (L'e daha az agirlik -> golge yutulur)
    bg = border_bg_color(lab)
    d = lab - bg
    dist = np.sqrt(0.35 * d[..., 0] ** 2 + 2.0 * d[..., 1] ** 2 + 2.0 * d[..., 2] ** 2)
    m_color = (dist > COLOR_T).astype(np.uint8)

    # 2) Kenar/doku enerjisi (parlak metal pinler renk farkiyla yakalanamaz)
    g = cv2.cvtColor(rgb, cv2.COLOR_RGB2GRAY)
    g = cv2.GaussianBlur(g, (5, 5), 0)
    gx = cv2.Sobel(g, cv2.CV_32F, 1, 0, ksize=3)
    gy = cv2.Sobel(g, cv2.CV_32F, 0, 1, ksize=3)
    mag = cv2.magnitude(gx, gy)
    m_edge = (mag > GRAD_T).astype(np.uint8)
    m_edge = cv2.morphologyEx(m_edge, cv2.MORPH_CLOSE, np.ones((9, 9), np.uint8))

    seed = cv2.bitwise_or(m_color, m_edge)

    # 3) Tohumu temizle ve cerceveye degen parcalari (masa kenari, dagiiklik) at
    k = max(3, int(min(h, w) * 0.003) | 1)
    seed = cv2.morphologyEx(seed, cv2.MORPH_OPEN, np.ones((k, k), np.uint8))
    seed = cv2.morphologyEx(seed, cv2.MORPH_CLOSE, np.ones((k, k), np.uint8))
    seed = drop_border_blobs(seed, h, w)
    seed = drop_small(seed, h * w * MIN_AREA)

    # 4) Sinirli buyume: zayif esik icinde, tohumdan sadece birkac piksel yayil
    #    (golge/arka planla birlesip tum kadraji yutmasini onler)
    weak = (dist > COLOR_T * 0.45).astype(np.uint8)
    weak = cv2.bitwise_or(weak, (mag > GRAD_T * 0.5).astype(np.uint8))
    mask = geodesic_grow(seed, weak, iters=12)

    # 5) SADECE kucuk delikleri doldur (pin aralari gercek arka plan -> dolmamali)
    mask = fill_small_holes(mask, h * w * 0.0015)
    mask = drop_small(mask, h * w * MIN_AREA)
    return mask


def geodesic_grow(seed, weak, iters=12):
    m = seed.copy()
    kern = np.ones((3, 3), np.uint8)
    for _ in range(iters):
        m = cv2.bitwise_and(cv2.dilate(m, kern), weak)
    return cv2.bitwise_or(m, seed)


def fill_small_holes(mask, max_hole_area):
    inv = 1 - mask
    n, lbl, stats, _ = cv2.connectedComponentsWithStats(inv, 4)
    out = mask.copy()
    h, w = mask.shape
    for i in range(1, n):
        x, y, bw, bh, area = stats[i]
        touches = x == 0 or y == 0 or x + bw >= w or y + bh >= h
        if not touches and area < max_hole_area:
            out[lbl == i] = 1
    return out


def drop_border_blobs(mask, h, w, margin=2):
    n, lbl, stats, _ = cv2.connectedComponentsWithStats(mask, 8)
    out = np.zeros_like(mask)
    for i in range(1, n):
        x, y, bw, bh, area = stats[i]
        touches = x <= margin or y <= margin or x + bw >= w - margin or y + bh >= h - margin
        if not touches:
            out[lbl == i] = 1
    return out if out.any() else mask


def drop_small(mask, min_area):
    n, lbl, stats, _ = cv2.connectedComponentsWithStats(mask, 8)
    out = np.zeros_like(mask)
    for i in range(1, n):
        if stats[i, cv2.CC_STAT_AREA] >= min_area:
            out[lbl == i] = 1
    return out


def soft_alpha(rgb, mask):
    """Renk anahtari: arka plan rengine yakin pikseller seffaf olur.
    Bu sayede pin araligi ve golge halesi otomatik temizlenir."""
    lab = cv2.cvtColor(rgb, cv2.COLOR_RGB2LAB).astype(np.float32)
    bg = border_bg_color(lab)
    d = lab - bg
    dist = np.sqrt(0.35 * d[..., 0] ** 2 + 2.0 * d[..., 1] ** 2 + 2.0 * d[..., 2] ** 2)

    lo, hi = ALPHA_LO, ALPHA_HI
    a = np.clip((dist - lo) / (hi - lo), 0, 1)

    # sadece urun bolgesinde gecerli olsun (arka plandaki dagiiklik silinsin)
    region = cv2.dilate(mask, np.ones((9, 9), np.uint8), iterations=2).astype(np.float32)
    region = cv2.GaussianBlur(region, (9, 9), 0)
    a = a * np.clip(region, 0, 1)

    a = cv2.GaussianBlur(a, (3, 3), 0)
    return (a * 255).astype(np.uint8), bg


def unmix(rgb, alpha, lab_bg):
    """Yari-saydam kenarlardaki fon rengi tortusunu temizle."""
    bg_rgb = cv2.cvtColor(lab_bg.reshape(1, 1, 3).astype(np.uint8),
                          cv2.COLOR_LAB2RGB).reshape(3).astype(np.float32)
    a = (alpha.astype(np.float32) / 255.0)[..., None]
    a_safe = np.maximum(a, 0.15)
    fg = (rgb.astype(np.float32) - bg_rgb * (1 - a)) / a_safe
    return np.clip(fg, 0, 255).astype(np.uint8)


def compose(rgb, alpha):
    ys, xs = np.where(alpha > 20)
    if len(xs) == 0:
        raise RuntimeError("urun bulunamadi - esikleri gevsetin")
    x0, x1, y0, y1 = xs.min(), xs.max(), ys.min(), ys.max()
    obj = rgb[y0:y1 + 1, x0:x1 + 1]
    a = alpha[y0:y1 + 1, x0:x1 + 1].astype(np.float32) / 255.0

    # beyaz zemine yerlestir
    flat = (obj.astype(np.float32) * a[..., None] +
            255.0 * (1 - a[..., None])).astype(np.uint8)

    oh, ow = flat.shape[:2]
    box_w, box_h = OUT_W * FILL_RATIO, OUT_H * FILL_RATIO
    s = min(box_w / ow, box_h / oh)
    nw, nh = max(1, int(ow * s)), max(1, int(oh * s))
    interp = cv2.INTER_AREA if s < 1 else cv2.INTER_LANCZOS4
    small = cv2.resize(flat, (nw, nh), interpolation=interp)
    amask = cv2.resize(a, (nw, nh), interpolation=interp)[..., None]

    canvas = np.full((OUT_H, OUT_W, 3), BG_COLOR, np.uint8)
    ox, oy = (OUT_W - nw) // 2, (OUT_H - nh) // 2
    roi = canvas[oy:oy + nh, ox:ox + nw].astype(np.float32)
    canvas[oy:oy + nh, ox:ox + nw] = (
        small.astype(np.float32) * amask + roi * (1 - amask)).astype(np.uint8)
    return canvas


def process(src, dst):
    rgb = load_rgb(src)
    mask = foreground_mask(rgb)
    alpha, lab_bg = soft_alpha(rgb, mask)
    rgb = unmix(rgb, alpha, lab_bg)
    out = compose(rgb, alpha)
    Image.fromarray(out).save(dst, "JPEG", quality=95, subsampling=0)


SUFFIX = "_trendyol"


def main():
    # Argüman verilmezse script'in bulundugu klasorde calisir
    here = os.path.dirname(os.path.abspath(__file__))
    ind = sys.argv[1] if len(sys.argv) > 1 else here
    outd = sys.argv[2] if len(sys.argv) > 2 else ind
    os.makedirs(outd, exist_ok=True)

    exts = ("jpg", "jpeg", "png", "webp", "heic")
    files = sorted({f for e in exts
                    for f in glob.glob(os.path.join(ind, "*." + e))
                    + glob.glob(os.path.join(ind, "*." + e.upper()))})
    # daha once islenmis ciktilari tekrar isleme
    files = [f for f in files if SUFFIX not in os.path.basename(f)]

    if not files:
        print("Gorsel bulunamadi:", ind)
    for f in files:
        name = os.path.splitext(os.path.basename(f))[0] + SUFFIX + ".jpg"
        dst = os.path.join(outd, name)
        try:
            process(f, dst)
            print("OK   ", name)
        except Exception as ex:
            print("HATA ", os.path.basename(f), "->", ex)
    print("\nBitti. Cikti klasoru:", outd)
    try:
        input("\nKapatmak icin Enter'a bas...")
    except EOFError:
        pass


if __name__ == "__main__":
    main()
