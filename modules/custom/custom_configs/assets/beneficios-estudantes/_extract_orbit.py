"""One-shot: extract orbit faces from collage.png into separate assets."""
from __future__ import annotations

import math
from pathlib import Path

import numpy as np
from PIL import Image, ImageDraw

ROOT = Path(__file__).resolve().parent
SRC = ROOT / "collage.png"


def main() -> None:
  im = Image.open(SRC).convert("RGBA")
  arr = np.array(im)
  alpha = arr[:, :, 3]
  h, w = alpha.shape
  cx, cy = (w - 1) / 2.0, (h - 1) / 2.0
  Y, X = np.ogrid[:h, :w]
  dist = np.sqrt((X - cx) ** 2 + (Y - cy) ** 2)
  mask = alpha > 200

  # Center crop: largest filled disk near image center.
  center_r = 95
  center = crop_circle(im, cx, cy, center_r, pad=8)
  center.save(ROOT / "collage-center.png")
  print("wrote collage-center.png", center.size)

  # Ring satellites: opaque pixels in annular band.
  ring = mask & (dist > 145) & (dist < 295)
  ry, rx = np.where(ring)
  ra = np.arctan2(ry - cy, rx - cx)
  # Cluster by angle into ~5 peaks.
  hist, edges = np.histogram(ra, bins=72, range=(-math.pi, math.pi))
  peaks: list[tuple[float, float]] = []
  for i, count in enumerate(hist):
    if count < 120:
      continue
    left = hist[i - 1] if i > 0 else hist[-1]
    right = hist[i + 1] if i < len(hist) - 1 else hist[0]
    if count >= left and count >= right:
      mid = (edges[i] + edges[i + 1]) / 2.0
      peaks.append((mid, float(count)))
  # Merge nearby peaks.
  peaks.sort(key=lambda p: p[0])
  merged: list[tuple[float, float]] = []
  for ang, count in peaks:
    if merged and abs(ang - merged[-1][0]) < (math.pi / 8):
      if count > merged[-1][1]:
        merged[-1] = (ang, count)
    else:
      merged.append((ang, count))
  # Also wrap-merge first/last.
  if len(merged) >= 2 and (merged[0][0] + 2 * math.pi - merged[-1][0]) < (math.pi / 8):
    if merged[0][1] >= merged[-1][1]:
      merged.pop()
    else:
      merged.pop(0)

  # Keep top 5 by count, sorted by angle clockwise from top (-pi/2).
  merged.sort(key=lambda p: -p[1])
  top = sorted(merged[:5], key=lambda p: (p[0] + math.pi / 2) % (2 * math.pi))
  print("peaks deg:", [round(math.degrees(a), 1) for a, _ in top])

  sat_r = 52
  for idx, (ang, _) in enumerate(top, start=1):
    # Estimate satellite center: mean of opaque pixels near this angle.
    dang = (ra - ang + math.pi) % (2 * math.pi) - math.pi
    sel = np.abs(dang) < (math.pi / 10)
    if not np.any(sel):
      sx = cx + math.cos(ang) * 210
      sy = cy + math.sin(ang) * 210
    else:
      sx = float(rx[sel].mean())
      sy = float(ry[sel].mean())
    sat = crop_circle(im, sx, sy, sat_r, pad=4)
    out = ROOT / f"collage-sat-{idx}.png"
    sat.save(out)
    print(f"wrote {out.name} center=({sx:.1f},{sy:.1f})")


def crop_circle(im: Image.Image, cx: float, cy: float, r: float, pad: int = 4) -> Image.Image:
  size = int(math.ceil(2 * r + 2 * pad))
  left = int(round(cx - r - pad))
  top = int(round(cy - r - pad))
  box = (left, top, left + size, top + size)
  crop = im.crop(box).convert("RGBA")
  mask = Image.new("L", (size, size), 0)
  draw = ImageDraw.Draw(mask)
  draw.ellipse((pad, pad, size - pad - 1, size - pad - 1), fill=255)
  out = Image.new("RGBA", (size, size), (0, 0, 0, 0))
  out.paste(crop, (0, 0), mask)
  return out


if __name__ == "__main__":
  main()
