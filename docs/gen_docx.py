# -*- coding: utf-8 -*-
from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_LINE_SPACING
from docx.enum.section import WD_SECTION
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

FONT = 'Arial Narrow'

doc = Document()

# ── Sección 0: portada + TOC (sin encabezado) ─────────────
s0 = doc.sections[0]
s0.top_margin    = Cm(2.54)
s0.bottom_margin = Cm(2.54)
s0.left_margin   = Cm(2.54)
s0.right_margin  = Cm(2.54)
s0.different_first_page_header_footer = False

# Estilo Normal base
n = doc.styles['Normal']
n.font.name = 'Arial Narrow'
n.font.size = Pt(11)
n.paragraph_format.alignment          = WD_ALIGN_PARAGRAPH.JUSTIFY
n.paragraph_format.line_spacing_rule  = WD_LINE_SPACING.MULTIPLE
n.paragraph_format.line_spacing       = 1.5
n.paragraph_format.space_after        = Pt(6)
n.paragraph_format.space_before       = Pt(0)

# ── HELPERS ───────────────────────────────────────────────

def _fmt(pf, before=0, after=6, indent=0, align=WD_ALIGN_PARAGRAPH.LEFT):
    pf.space_before       = Pt(before)
    pf.space_after        = Pt(after)
    pf.first_line_indent  = Cm(indent)
    pf.alignment          = align
    pf.line_spacing_rule  = WD_LINE_SPACING.MULTIPLE
    pf.line_spacing       = 1.5

def _run(p, text, size=11, bold=False, italic=False):
    r = p.add_run(text)
    r.font.name   = FONT
    r.font.size   = Pt(size)
    r.font.bold   = bold
    r.font.italic = italic
    return r

def h1(text):
    p = doc.add_paragraph()
    _fmt(p.paragraph_format, before=24, after=12)
    _run(p, text.upper(), size=16, bold=True)
    return p

def h2(text):
    p = doc.add_paragraph()
    _fmt(p.paragraph_format, before=18, after=6)
    _run(p, text, size=13, bold=True)
    return p

def h3(text):
    p = doc.add_paragraph()
    _fmt(p.paragraph_format, before=14, after=5)
    _run(p, text, size=12, bold=True, italic=True)
    return p

def h4(text):
    p = doc.add_paragraph()
    _fmt(p.paragraph_format, before=10, after=4)
    _run(p, text, size=11, italic=True)
    return p

def body(text, first=False, bold_prefix=None):
    p = doc.add_paragraph()
    _fmt(p.paragraph_format, before=0, after=6,
         indent=0 if first else 1.27,
         align=WD_ALIGN_PARAGRAPH.JUSTIFY)
    if bold_prefix:
        _run(p, bold_prefix, bold=True)
        _run(p, ' ' + text)
    else:
        _run(p, text)
    return p

def bullet(text, bold_prefix=None):
    p = doc.add_paragraph(style='List Bullet')
    _fmt(p.paragraph_format, before=0, after=3,
         align=WD_ALIGN_PARAGRAPH.JUSTIFY)
    p.paragraph_format.first_line_indent = Cm(0)
    if bold_prefix:
        _run(p, bold_prefix, bold=True)
        _run(p, ' ' + text)
    else:
        _run(p, text)
    # fix font on bullet style
    for r in p.runs:
        r.font.name = FONT
        r.font.size = Pt(11)
    return p

def pb():
    """Page break"""
    p = doc.add_paragraph()
    p.paragraph_format.space_after  = Pt(0)
    p.paragraph_format.space_before = Pt(0)
    r = p.add_run()
    r.add_break(__import__('docx.enum.text', fromlist=['WD_BREAK']).WD_BREAK.PAGE)
    return p

def tech_note(text, label='Nota técnica'):
    p = doc.add_paragraph()
    _fmt(p.paragraph_format, before=6, after=6,
         align=WD_ALIGN_PARAGRAPH.JUSTIFY)
    p.paragraph_format.left_indent  = Cm(0.5)
    p.paragraph_format.right_indent = Cm(0.5)
    _run(p, label + ': ', bold=True, italic=False, size=10)
    r = p.add_run(text)
    r.font.name   = FONT
    r.font.size   = Pt(10)
    r.font.italic = True
    # left border via XML
    pPr = p._p.get_or_add_pPr()
    pBdr = OxmlElement('w:pBdr')
    left = OxmlElement('w:left')
    left.set(qn('w:val'),   'single')
    left.set(qn('w:sz'),    '12')
    left.set(qn('w:color'), '999999')
    pBdr.append(left)
    pPr.append(pBdr)
    return p

def figure_ph(num, title):
    """APA figure placeholder"""
    tbl = doc.add_table(rows=1, cols=1)
    tbl.alignment = WD_TABLE_ALIGNMENT if False else 0
    cell = tbl.cell(0, 0)
    cp = cell.paragraphs[0]
    cp.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = cp.add_run(f'[Figura {num} — Pendiente de elaboración en herramienta gráfica]')
    r.font.name   = FONT
    r.font.size   = Pt(10)
    r.font.italic = True
    # dashed border on cell
    tc   = cell._tc
    tcPr = tc.get_or_add_tcPr()
    tcBd = OxmlElement('w:tcBorders')
    for side in ['top','left','bottom','right']:
        el = OxmlElement(f'w:{side}')
        el.set(qn('w:val'),   'dashed')
        el.set(qn('w:sz'),    '6')
        el.set(qn('w:color'), '888888')
        tcBd.append(el)
    tcPr.append(tcBd)
    # caption below
    cp2 = doc.add_paragraph()
    _fmt(cp2.paragraph_format, before=3, after=8, align=WD_ALIGN_PARAGRAPH.LEFT)
    _run(cp2, f'Figura {num}.', bold=True, size=11)
    _run(cp2, f' {title}', italic=True, size=11)
    return tbl

def _cell_borders_none(cell):
    tc   = cell._tc
    tcPr = tc.get_or_add_tcPr()
    tcBd = OxmlElement('w:tcBorders')
    for side in ['top','left','bottom','right','insideH','insideV']:
        el = OxmlElement(f'w:{side}')
        el.set(qn('w:val'), 'none')
        tcBd.append(el)
    tcPr.append(tcBd)

def _set_cell_shading(cell, fill='F0F0F0'):
    tc   = cell._tc
    tcPr = tc.get_or_add_tcPr()
    shd  = OxmlElement('w:shd')
    shd.set(qn('w:val'),   'clear')
    shd.set(qn('w:color'), 'auto')
    shd.set(qn('w:fill'),  fill)
    tcPr.append(shd)

def _tbl_borders(table):
    """APA: top border, bottom border on table; bottom border under header row; no verticals."""
    tbl  = table._tbl
    tblPr = tbl.tblPr
    # remove existing tblBorders
    for old in tblPr.findall(qn('w:tblBorders')):
        tblPr.remove(old)
    tblBd = OxmlElement('w:tblBorders')
    for side, val, sz in [
        ('top',     'single', '12'),
        ('bottom',  'single', '12'),
        ('left',    'none',   '0'),
        ('right',   'none',   '0'),
        ('insideH', 'none',   '0'),
        ('insideV', 'none',   '0'),
    ]:
        el = OxmlElement(f'w:{side}')
        el.set(qn('w:val'),   val)
        el.set(qn('w:sz'),    sz)
        el.set(qn('w:color'), '000000')
        tblBd.append(el)
    tblPr.append(tblBd)

    # bottom border on each header cell
    for cell in table.rows[0].cells:
        tc   = cell._tc
        tcPr = tc.get_or_add_tcPr()
        tcBd = OxmlElement('w:tcBorders')
        bot  = OxmlElement('w:bottom')
        bot.set(qn('w:val'),   'single')
        bot.set(qn('w:sz'),    '6')
        bot.set(qn('w:color'), '000000')
        tcBd.append(bot)
        for side in ['top','left','right','insideH','insideV']:
            el = OxmlElement(f'w:{side}')
            el.set(qn('w:val'), 'none')
            tcBd.append(el)
        tcPr.append(tcBd)

    # no borders on data rows
    for row in table.rows[1:]:
        for cell in row.cells:
            _cell_borders_none(cell)

def apa_table(num, title, headers, rows, note=None):
    """Create APA 7 formatted table."""
    # Label
    lp = doc.add_paragraph()
    _fmt(lp.paragraph_format, before=10, after=1, align=WD_ALIGN_PARAGRAPH.LEFT)
    _run(lp, f'Tabla {num}', bold=True)
    # Title
    tp = doc.add_paragraph()
    _fmt(tp.paragraph_format, before=0, after=4, align=WD_ALIGN_PARAGRAPH.LEFT)
    _run(tp, title, italic=True)

    ncols = len(headers)
    tbl   = doc.add_table(rows=1 + len(rows), cols=ncols)
    tbl.style = 'Table Grid'  # start with grid, override below

    # Header row
    hdr = tbl.rows[0]
    for i, h in enumerate(headers):
        c = hdr.cells[i]
        _set_cell_shading(c, 'F0F0F0')
        cp = c.paragraphs[0]
        cp.alignment = WD_ALIGN_PARAGRAPH.LEFT
        r  = cp.add_run(h)
        r.font.name  = FONT
        r.font.size  = Pt(10)
        r.font.bold  = True

    # Data rows
    for ri, row in enumerate(rows):
        tr = tbl.rows[ri + 1]
        for ci, cell_text in enumerate(row):
            c  = tr.cells[ci]
            cp = c.paragraphs[0]
            cp.alignment = WD_ALIGN_PARAGRAPH.LEFT
            r  = cp.add_run(str(cell_text))
            r.font.name = FONT
            r.font.size = Pt(10)

    _tbl_borders(tbl)

    if note:
        np2 = doc.add_paragraph()
        _fmt(np2.paragraph_format, before=2, after=8, align=WD_ALIGN_PARAGRAPH.LEFT)
        _run(np2, 'Nota. ', italic=True, size=9)
        r2 = np2.add_run(note)
        r2.font.name   = FONT
        r2.font.size   = Pt(9)
        r2.font.italic = True
    else:
        sp = doc.add_paragraph()
        _fmt(sp.paragraph_format, before=0, after=8)

    return tbl

def usecase_table(uc_title, rows):
    """Use case box as a single-column table with title row."""
    # title
    tp = doc.add_paragraph()
    _fmt(tp.paragraph_format, before=8, after=2, align=WD_ALIGN_PARAGRAPH.LEFT)
    _run(tp, uc_title, bold=True, size=11)

    tbl = doc.add_table(rows=len(rows), cols=2)
    tbl.style = 'Table Grid'
    col_widths = [Cm(4), Cm(12)]
    for ri, (label, val) in enumerate(rows):
        row = tbl.rows[ri]
        c0, c1 = row.cells[0], row.cells[1]
        _set_cell_shading(c0, 'F0F0F0')
        for cell, txt, bold in [(c0, label, True), (c1, val, False)]:
            cp = cell.paragraphs[0]
            cp.alignment = WD_ALIGN_PARAGRAPH.LEFT
            r  = cp.add_run(txt)
            r.font.name  = FONT
            r.font.size  = Pt(10)
            r.font.bold  = bold

    # APA borders
    _tbl_borders(tbl)
    sp = doc.add_paragraph()
    _fmt(sp.paragraph_format, before=0, after=10)
    return tbl

def add_running_header(section, text):
    """Add running header text to a section."""
    header = section.header
    header.is_linked_to_previous = False
    hp = header.paragraphs[0] if header.paragraphs else header.add_paragraph()
    hp.clear()
    hp.alignment = WD_ALIGN_PARAGRAPH.LEFT
    r = hp.add_run(text)
    r.font.name  = FONT
    r.font.size  = Pt(9)
    # bottom border on header
    pPr  = hp._p.get_or_add_pPr()
    pBdr = OxmlElement('w:pBdr')
    bot  = OxmlElement('w:bottom')
    bot.set(qn('w:val'),   'single')
    bot.set(qn('w:sz'),    '4')
    bot.set(qn('w:color'), 'CCCCCC')
    pBdr.append(bot)
    pPr.append(pBdr)

def add_page_number_footer(section):
    """Page number in footer, right-aligned."""
    footer = section.footer
    footer.is_linked_to_previous = False
    fp = footer.paragraphs[0] if footer.paragraphs else footer.add_paragraph()
    fp.clear()
    fp.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    run = fp.add_run()
    run.font.name = FONT
    run.font.size = Pt(9)
    fldChar1 = OxmlElement('w:fldChar')
    fldChar1.set(qn('w:fldCharType'), 'begin')
    instrText = OxmlElement('w:instrText')
    instrText.set(qn('xml:space'), 'preserve')
    instrText.text = 'PAGE'
    fldChar2 = OxmlElement('w:fldChar')
    fldChar2.set(qn('w:fldCharType'), 'end')
    run._r.append(fldChar1)
    run._r.append(instrText)
    run._r.append(fldChar2)

def toc_line(text, page, level=1):
    p = doc.add_paragraph()
    indent = {1: 0, 2: 0.5, 3: 1.0}[level]
    pf = p.paragraph_format
    pf.space_before = Pt(2)
    pf.space_after  = Pt(2)
    pf.alignment    = WD_ALIGN_PARAGRAPH.LEFT
    pf.left_indent  = Cm(indent)
    pf.line_spacing_rule = WD_LINE_SPACING.MULTIPLE
    pf.line_spacing = 1.2
    # Tab stop at right margin for page number
    from docx.oxml import OxmlElement as OE
    tabs_el = OE('w:tabs')
    tab = OE('w:tab')
    tab.set(qn('w:val'),    'right')
    tab.set(qn('w:pos'),    '9071')  # ~16cm in twips
    tab.set(qn('w:leader'), 'dot')
    tabs_el.append(tab)
    p._p.get_or_add_pPr().append(tabs_el)
    bold = level == 1
    _run(p, text, bold=bold)
    _run(p, '\t' + str(page), bold=bold)
    return p

# ═══════════════════════════════════════════════════════════
#  PORTADA
# ═══════════════════════════════════════════════════════════
for _ in range(8):
    doc.add_paragraph()  # vertical space

pc = doc.add_paragraph()
pc.alignment = WD_ALIGN_PARAGRAPH.CENTER
r = pc.add_run('SISTEMA DE SOLICITUD DE RAMOS')
r.font.name = FONT; r.font.size = Pt(18); r.font.bold = True

pc2 = doc.add_paragraph()
pc2.alignment = WD_ALIGN_PARAGRAPH.CENTER
_run(pc2, 'Documentación Técnica del Sistema', size=14)

for _ in range(6):
    doc.add_paragraph()

for line in [
    ('Flores el Tandil', 12, True),
    ('', 12, False),
    ('Mario Alexander Cañola Cano', 12, False),
    ('Analista y Desarrollador de Software', 12, False),
    ('', 12, False),
    ('Versión 1.0 — Estado: Etapa de Pruebas', 12, False),
    ('Mayo 2026', 12, False),
    ('Tandil', 12, False),
]:
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_after = Pt(4)
    _run(p, line[0], size=line[1], bold=line[2])

# ═══════════════════════════════════════════════════════════
#  TABLA DE CONTENIDO
# ═══════════════════════════════════════════════════════════
pb()

tc_title = doc.add_paragraph()
tc_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
_fmt(tc_title.paragraph_format, before=0, after=12)
_run(tc_title, 'TABLA DE CONTENIDO', size=16, bold=True)

toc_data = [
    (1,'1. Introducción',1),
    (2,'1.1 Descripción General',1),
    (3,'1.1.1 Breve Resumen del Proyecto',1),
    (3,'1.1.2 Justificación y Necesidad del Software',2),
    (2,'1.2 Objetivos',3),
    (3,'1.2.1 Objetivo General',3),
    (3,'1.2.2 Objetivos Específicos',3),
    (2,'1.3 Alcance del Proyecto',4),
    (3,'1.3.1 Funcionalidades Principales del Software',4),
    (3,'1.3.2 Usuarios Finales y Stakeholders',6),
    (3,'1.3.3 Limitaciones o Restricciones',7),
    (1,'2. Marco Teórico y Estado del Arte',9),
    (2,'2.1 Antecedentes del Problema',9),
    (3,'2.1.1 Explicación del Problema y Contexto',9),
    (3,'2.1.2 Comparación con Soluciones Existentes',10),
    (2,'2.2 Referentes Teóricos',11),
    (3,'2.2.1 Principios, Metodologías y Enfoques Aplicables',11),
    (3,'2.2.2 Normas y Estándares de Calidad',12),
    (2,'2.3 Tecnologías Utilizadas',13),
    (3,'2.3.1 Lenguajes de Programación',13),
    (3,'2.3.2 Frameworks y Herramientas',13),
    (3,'2.3.3 Bases de Datos y Servicios',14),
    (1,'3. Análisis de Requisitos',15),
    (2,'3.1 Caracterización del Negocio',15),
    (3,'3.1.1 Procesos Actuales del Negocio',15),
    (3,'3.1.2 Diagrama de Procesos BPMN',16),
    (3,'3.1.3 Técnicas Utilizadas',17),
    (3,'3.1.4 Fuentes de Información',17),
    (2,'3.2 Especificación de Requisitos',18),
    (3,'3.2.1 Requisitos Funcionales',18),
    (3,'3.2.2 Requisitos No Funcionales',20),
    (2,'3.3 Modelado de Requisitos',21),
    (3,'3.3.1 Diagramas de Casos de Uso',21),
    (3,'3.3.2 Modelo Entidad-Relación',23),
    (3,'3.3.3 Prototipos y Wireframes de la Interfaz',24),
    (1,'4. Diseño del Software',26),
    (2,'4.1 Arquitectura del Software',26),
    (3,'4.1.1 Modelo Arquitectónico',26),
    (3,'4.1.2 Componentes Principales del Sistema',28),
    (2,'4.2 Diseño de la Base de Datos',29),
    (3,'4.2.1 Modelo Lógico de la Base de Datos',29),
    (3,'4.2.2 Diccionario de Datos',30),
    (2,'4.3 Interfaz de Usuario',35),
    (3,'4.3.1 Diseño UX/UI',35),
    (3,'4.3.2 Wireframes o Prototipos',36),
    (1,'5. Plan de Desarrollo',37),
    (2,'5.1 Metodología de Desarrollo',37),
    (2,'5.2 Plan de Iteraciones y Sprints',38),
    (2,'5.3 Herramientas de Desarrollo',39),
    (1,'6. Pruebas y Validación',40),
    (2,'6.1 Plan de Pruebas',40),
    (2,'6.2 Resultados de Pruebas',41),
    (2,'6.3 Validación con el Cliente',42),
    (1,'7. Implementación y Mantenimiento',43),
    (2,'7.1 Estrategia de Implementación',43),
    (2,'7.2 Plan de Capacitación',44),
    (2,'7.3 Mantenimiento del Software',45),
    (1,'8. Conclusiones y Recomendaciones',46),
    (2,'8.1 Evaluación del Proyecto',46),
    (2,'8.2 Lecciones Aprendidas',47),
    (2,'8.3 Bibliografía y Referencias',48),
]
for level, text, page in toc_data:
    toc_line(text, page, level)

# ═══════════════════════════════════════════════════════════
#  SECCIÓN 1: contenido con encabezado corriente
# ═══════════════════════════════════════════════════════════
doc.add_section(WD_SECTION.NEW_PAGE)
s1 = doc.sections[-1]
s1.top_margin    = Cm(2.54)
s1.bottom_margin = Cm(2.54)
s1.left_margin   = Cm(2.54)
s1.right_margin  = Cm(2.54)
add_running_header(s1, 'SISTEMA DE SOLICITUD DE RAMOS')
add_page_number_footer(s1)

print("Setup OK, writing chapters...")
