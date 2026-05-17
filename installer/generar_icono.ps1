param([string]$OutPath)

# Genera un icono de flor de 48x48 px y lo guarda como .ico
# Requiere .NET / System.Drawing (disponible en cualquier Windows moderno)

Add-Type -AssemblyName System.Drawing

$size = 48
$bmp = New-Object System.Drawing.Bitmap($size, $size,
        [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
$g   = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.Clear([System.Drawing.Color]::Transparent)

$cx = 24; $cy = 24

# --- 6 petalos rosa / fucsia ---
$petalBrush = New-Object System.Drawing.SolidBrush(
    [System.Drawing.Color]::FromArgb(255, 210, 60, 100))

for ($i = 0; $i -lt 6; $i++) {
    $a  = $i * [Math]::PI / 3.0
    $px = [int][Math]::Round($cx + [Math]::Cos($a) * 13.0)
    $py = [int][Math]::Round($cy + [Math]::Sin($a) * 13.0)
    $g.FillEllipse($petalBrush, ($px - 9), ($py - 9), 18, 18)
}

# --- Centro amarillo dorado ---
$centerBrush = New-Object System.Drawing.SolidBrush(
    [System.Drawing.Color]::FromArgb(255, 255, 210, 0))
$g.FillEllipse($centerBrush, 15, 15, 18, 18)

# --- Borde sutil en el centro ---
$borderPen = New-Object System.Drawing.Pen(
    [System.Drawing.Color]::FromArgb(100, 160, 110, 0), 1)
$g.DrawEllipse($borderPen, 15, 15, 18, 18)

$g.Dispose()

# Guardar bitmap como PNG en memoria
$ms = New-Object System.IO.MemoryStream
$bmp.Save($ms, [System.Drawing.Imaging.ImageFormat]::Png)
$pngBytes = $ms.ToArray()
$ms.Dispose()
$bmp.Dispose()

# Construir archivo .ico con PNG embebido (compatible Windows Vista+)
# Estructura: ICO header (6 bytes) + ICONDIRENTRY (16 bytes) + datos PNG
$fs = [System.IO.File]::Create($OutPath)
$bw = New-Object System.IO.BinaryWriter($fs)

# ICO header
$bw.Write([uint16]0)   # Reserved
$bw.Write([uint16]1)   # Type: 1 = ICO
$bw.Write([uint16]1)   # Numero de imagenes

# ICONDIRENTRY
$bw.Write([byte]$size)                    # Ancho
$bw.Write([byte]$size)                    # Alto
$bw.Write([byte]0)                        # ColorCount (0 = sin paleta)
$bw.Write([byte]0)                        # Reserved
$bw.Write([uint16]1)                      # Planes
$bw.Write([uint16]32)                     # BitCount
$bw.Write([uint32]$pngBytes.Length)       # Tamano del bloque de imagen
$bw.Write([uint32]22)                     # Offset: 6 (header) + 16 (entry)

# Datos de imagen (PNG)
$bw.Write($pngBytes)
$bw.Close()
$fs.Close()

Write-Host "  Icono generado: $OutPath"
