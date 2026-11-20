#!/usr/bin/env python3
"""Gera as artes WebP do bloco "Como funciona" (desktop + mobile) em alta resolucao.

As duas artes sao redesenhadas em vetor (supersampling 3x + LANCZOS), sem
reaproveitar o raster antigo de 1024px (que ficava desfocado acima de 1024px).

- como-funciona-desktop-hd.webp : fluxo horizontal (7 chevrons), ~2x da largura
  maxima exibida (1150px) para telas retina.
- como-funciona-mobile-v2.webp  : versao adaptada, fluxo vertical (chevrons
  apontando para baixo), legivel em 360-430px de largura.

Uso:
    python generate_images.py [diretorio_com_fontes_Poppins]

Fontes: Poppins-Regular.ttf, Poppins-Medium.ttf e Poppins-Bold.ttf (OFL).
Por padrao procura em ./fonts, %TEMP%/poppins e nas fontes do sistema.
"""

from __future__ import annotations

import math
import os
import sys
import tempfile
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

HERE = Path(__file__).resolve().parent
SS = 3  # supersampling

COLORS = [
  "#0d3b72",
  "#2571be",
  "#21a9c6",
  "#2ecf9b",
  "#5dd86a",
  "#f4ca3f",
  "#fb9742",
]
TEXT_COLOR = "#1c1c1c"
NUMBER_COLOR = "#ededed"

STEPS = [
  ["Entendimento", "da empresa"],
  ["Mapeamento", "da vaga"],
  ["Seleção", "estratégica"],
  ["Apresentação", "dos melhores", "candidatos"],
  ["Contratação"],
  ["Gestão", "completa"],
  ["Desenvolvimento", "contínuo"],
]

STEPS_MOBILE = [
  ["Entendimento da empresa"],
  ["Mapeamento da vaga"],
  ["Seleção estratégica"],
  ["Apresentação dos melhores", "candidatos"],
  ["Contratação"],
  ["Gestão completa"],
  ["Desenvolvimento contínuo"],
]


def find_font_dir() -> Path:
  candidates = []
  if len(sys.argv) > 1:
    candidates.append(Path(sys.argv[1]))
  candidates += [HERE / "fonts", Path(tempfile.gettempdir()) / "poppins"]
  for c in candidates:
    if (c / "Poppins-Regular.ttf").is_file():
      return c
  raise SystemExit("Poppins-*.ttf nao encontrada. Passe o diretorio como argumento.")


FONT_DIR = find_font_dir()


def font(weight: str, size_units: float, scale: float) -> ImageFont.FreeTypeFont:
  return ImageFont.truetype(str(FONT_DIR / f"Poppins-{weight}.ttf"), int(round(size_units * scale)))


def hex_rgba(h: str, a: int = 255) -> tuple[int, int, int, int]:
  h = h.lstrip("#")
  return int(h[0:2], 16), int(h[2:4], 16), int(h[4:6], 16), a


class Canvas:
  """Desenha em unidades de design; `scale` converte para pixels finais."""

  def __init__(self, w_units: float, h_units: float, scale: float):
    self.scale = scale
    self.k = scale * SS
    self.size = (int(round(w_units * scale)), int(round(h_units * scale)))
    self.img = Image.new("RGBA", (self.size[0] * SS, self.size[1] * SS), (0, 0, 0, 0))
    self.d = ImageDraw.Draw(self.img)

  def p(self, pt):
    return (pt[0] * self.k, pt[1] * self.k)

  def polyline(self, pts, width, color):
    px = [self.p(q) for q in pts]
    w = max(1, int(round(width * self.k)))
    self.d.line(px, fill=color, width=w, joint="curve")
    r = w / 2
    for x, y in (px[0], px[-1]):  # pontas arredondadas
      self.d.ellipse((x - r, y - r, x + r, y + r), fill=color)

  def arrow(self, start, end, width, color, head=7.0):
    self.polyline([start, end], width, color)
    ang = math.atan2(end[1] - start[1], end[0] - start[0])
    for s in (+1, -1):
      a = ang + math.pi + s * math.radians(38)
      tip = (end[0] + head * math.cos(a), end[1] + head * math.sin(a))
      self.polyline([end, tip], width, color)

  def text_lines(self, lines, cx, cy, size, line_h, weight="Regular", fill=TEXT_COLOR):
    f = font(weight, size, self.k)
    top = cy - (len(lines) - 1) * line_h / 2
    for i, line in enumerate(lines):
      x, y = self.p((cx, top + i * line_h))
      self.d.text((x, y), line, font=f, fill=hex_rgba(fill) if fill.startswith("#") else fill, anchor="mm")

  def number(self, text, right, baseline, size, color=NUMBER_COLOR):
    f = font("Bold", size, self.k)
    x, y = self.p((right, baseline))
    self.d.text((x, y), text, font=f, fill=hex_rgba(color), anchor="rs")

  def save(self, dest: Path):
    out = self.img.resize(self.size, Image.Resampling.LANCZOS)
    out.save(dest, "WEBP", quality=92, method=6, alpha_quality=100)
    print(f"Wrote {dest.name} {out.size} {dest.stat().st_size // 1024} KB")


# Area segura do texto no chevron desktop (unidades relativas a x0 do passo):
# vai de ~28 (ponta do recorte esquerdo + traco) a ~140 (diagonal direita).
TEXT_CENTER = 84.0
TEXT_MAX_W = 106.0


def desktop() -> None:
  pitch, W, n, d = 146.0, 154.0, 24.0, 24.0
  T, B = 7.0, 116.0
  mid = (T + B) / 2
  margin = 3.0
  stroke = 3.4
  total_w = 6 * pitch + W + margin * 2
  total_h = 122.0
  scale = 2.25  # 1 unidade = 2.25px -> ~2333 x 275

  c = Canvas(total_w, total_h, scale)

  # Tamanho unico: a maior linha de qualquer etapa deve caber na area segura.
  base = 14.2
  ref = font("Regular", base, 10)
  widest = max(ref.getlength(line) / 10 for label in STEPS for line in label)
  text_size = min(base, base * TEXT_MAX_W / widest)
  print(f"texto: {text_size:.2f}u (linha mais larga {widest:.1f}u a {base}u)")

  for i, label in enumerate(STEPS):
    x0 = margin + i * pitch
    color = hex_rgba(COLORS[i])

    # numero decorativo ao fundo
    c.number(str(i + 1), x0 + W - 20, B - 8, 96)

  for i, label in enumerate(STEPS):
    x0 = margin + i * pitch
    color = hex_rgba(COLORS[i])

    pts = [
      (x0, T),
      (x0 + n, mid),
      (x0, B),
      (x0 + W - d, B),
      (x0 + W, mid),
      (x0 + W - d, T),
    ]
    if i < len(STEPS) - 1:
      pts.append((x0 + pitch, T))
    c.polyline(pts, stroke, color)

    # seta fina de progresso no topo
    c.arrow((x0 + 22, T), (x0 + 118, T), 1.5, color, head=6.5)

    # texto centralizado na area util (entre a ponta do recorte esquerdo e a
    # diagonal direita), sempre inteiramente dentro do desenho
    cx = x0 + TEXT_CENTER
    line_h = 20.5
    c.text_lines(label, cx, mid + 3, text_size, line_h)

  c.save(HERE / "como-funciona-desktop-hd.webp")


def mobile() -> None:
  Wv, n, d, gap = 112.0, 34.0, 34.0, 12.0
  pitch = Wv - d + gap
  L, R = 6.0, 334.0
  cx = (L + R) / 2
  margin_y = 4.0
  stroke = 3.4
  total_w = R + 6.0
  total_h = 6 * pitch + Wv + margin_y * 2
  scale = 3.0  # 1 unidade = 3px -> ~1020px de largura (retina em 340px)

  c = Canvas(total_w, total_h, scale)

  for i in range(len(STEPS_MOBILE)):
    y0 = margin_y + i * pitch
    c.number(str(i + 1), R - 14, y0 + Wv - d - 6, 78)

  for i, label in enumerate(STEPS_MOBILE):
    y0 = margin_y + i * pitch
    color = hex_rgba(COLORS[i])

    pts = [
      (L, y0),
      (cx, y0 + n),
      (R, y0),
      (R, y0 + Wv - d),
      (cx, y0 + Wv),
      (L, y0 + Wv - d),
    ]
    if i < len(STEPS_MOBILE) - 1:
      pts.append((L, y0 + pitch))
    c.polyline(pts, stroke, color)

    # seta fina de progresso (lado esquerdo, apontando para o proximo passo)
    c.arrow((L, y0 + 14), (L, y0 + Wv - d - 10), 1.5, color, head=8.5)

    cy = y0 + (n + Wv - d) / 2 + 2
    c.text_lines(label, cx, cy, 16.5, 23.0)

  c.save(HERE / "como-funciona-mobile-v2.webp")


if __name__ == "__main__":
  desktop()
  mobile()
