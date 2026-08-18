# -*- coding: utf-8 -*-
"""
Genera el diagrama ER en formato draw.io (.drawio)
Abrir en: https://app.diagrams.net  o  Draw.io Desktop
Archivo: docs/er_flores_db.drawio
"""

# ── Colores por categoría ─────────────────────────────────
C_CENTRAL   = {'fill': '#fff2cc', 'stroke': '#d6b656', 'font': '#000'}  # solicitudes
C_CATALOGO  = {'fill': '#dae8fc', 'stroke': '#6c8ebf', 'font': '#000'}  # sedes, motivos, estados
C_PERSONAS  = {'fill': '#d5e8d4', 'stroke': '#82b366', 'font': '#000'}  # personas, usuarios
C_CONFIG    = {'fill': '#f8cecc', 'stroke': '#b85450', 'font': '#000'}  # configuracion, log_correos
C_CUPOS     = {'fill': '#e1d5e7', 'stroke': '#9673a6', 'font': '#000'}  # cupos_sede

W = 260   # table width
RH = 26   # row height
HH = 30   # header height

def table_height(fields):
    return HH + len(fields) * RH

# ── Definición de tablas ──────────────────────────────────
TABLES = [
    {
        'id': 'configuracion', 'name': 'configuracion',
        'x': 20, 'y': 20, 'color': C_CONFIG,
        'fields': [
            ('id',          'INT',          'PK'),
            ('clave',       'VARCHAR(50)',   'UK'),
            ('valor',       'TEXT',          ''),
            ('descripcion', 'VARCHAR(255)',  'NULL'),
        ]
    },
    {
        'id': 'usuarios', 'name': 'usuarios',
        'x': 630, 'y': 20, 'color': C_PERSONAS,
        'fields': [
            ('id',            'INT',          'PK'),
            ('username',      'VARCHAR(50)',  'UK'),
            ('password_hash', 'VARCHAR(255)', ''),
            ('nombre',        'VARCHAR(100)', ''),
            ('rol',           'ENUM',         ''),
            ('activo',        'TINYINT(1)',   ''),
            ('ultimo_login',  'DATETIME',     'NULL'),
            ('created_at',    'DATETIME',     ''),
            ('updated_at',    'DATETIME',     ''),
        ]
    },
    {
        'id': 'sedes', 'name': 'sedes',
        'x': 325, 'y': 230, 'color': C_CATALOGO,
        'fields': [
            ('id',         'INT',          'PK'),
            ('nombre',     'VARCHAR(100)', 'UK'),
            ('codigo',     'VARCHAR(20)',  'UK'),
            ('direccion',  'VARCHAR(255)', 'NULL'),
            ('activo',     'TINYINT(1)',  ''),
            ('created_at', 'DATETIME',    ''),
            ('updated_at', 'DATETIME',    ''),
        ]
    },
    {
        'id': 'cupos_sede', 'name': 'cupos_sede',
        'x': 20, 'y': 230, 'color': C_CUPOS,
        'fields': [
            ('id',           'INT',       'PK'),
            ('id_sede',      'INT',       'FK→sedes'),
            ('periodo',      'DATE',      'UK+'),
            ('cupo_maximo',  'INT',       ''),
            ('notificado',   'TINYINT(1)',''),
            ('created_at',   'DATETIME',  ''),
            ('updated_at',   'DATETIME',  ''),
        ]
    },
    {
        'id': 'personas', 'name': 'personas',
        'x': 630, 'y': 230, 'color': C_PERSONAS,
        'fields': [
            ('id',              'INT',         'PK'),
            ('tipo_documento',  'ENUM',        ''),
            ('documento',       'VARCHAR(20)', 'UK'),
            ('primer_nombre',   'VARCHAR(50)', ''),
            ('segundo_nombre',  'VARCHAR(50)', 'NULL'),
            ('primer_apellido', 'VARCHAR(50)', ''),
            ('segundo_apellido','VARCHAR(50)', 'NULL'),
            ('telefono',        'VARCHAR(20)', 'NULL'),
            ('id_sede',         'INT',         'FK→sedes'),
            ('empresa',         'ENUM',        ''),
            ('activo',          'TINYINT(1)',  ''),
            ('created_at',      'DATETIME',    ''),
            ('updated_at',      'DATETIME',    ''),
        ]
    },
    {
        'id': 'motivos_ramo', 'name': 'motivos_ramo',
        'x': 20, 'y': 630, 'color': C_CATALOGO,
        'fields': [
            ('id',               'INT',          'PK'),
            ('nombre',           'VARCHAR(100)', 'UK'),
            ('requiere_detalle', 'TINYINT(1)',   ''),
            ('orden',            'INT',           ''),
            ('activo',           'TINYINT(1)',   ''),
        ]
    },
    {
        'id': 'solicitudes', 'name': 'solicitudes',
        'x': 325, 'y': 630, 'color': C_CENTRAL,
        'fields': [
            ('id',                 'INT',          'PK'),
            ('persona_id',         'INT',          'FK→personas'),
            ('fecha_solicitud',    'DATE',          ''),
            ('id_sede',            'INT',           'FK→sedes'),
            ('nombre_destinatario','VARCHAR(150)',  ''),
            ('id_motivo',          'INT',           'FK→motivos_ramo'),
            ('motivo_otro',        'VARCHAR(200)',  'NULL'),
            ('observaciones',      'TEXT',          'NULL'),
            ('id_estado',          'INT',           'FK→estados_solicitud'),
            ('created_at',         'DATETIME',      ''),
            ('updated_at',         'DATETIME',      ''),
        ]
    },
    {
        'id': 'estados_solicitud', 'name': 'estados_solicitud',
        'x': 630, 'y': 630, 'color': C_CATALOGO,
        'fields': [
            ('id',     'INT',         'PK'),
            ('nombre', 'VARCHAR(50)', 'UK'),
            ('color',  'VARCHAR(7)',  ''),
            ('orden',  'INT',         ''),
        ]
    },
    {
        'id': 'log_correos', 'name': 'log_correos',
        'x': 325, 'y': 1050, 'color': C_CONFIG,
        'fields': [
            ('id',           'INT',          'PK'),
            ('tipo',         'ENUM',         ''),
            ('destinatario', 'VARCHAR(255)', ''),
            ('asunto',       'VARCHAR(255)', ''),
            ('estado',       'ENUM',         ''),
            ('error_detalle','TEXT',          'NULL'),
            ('created_at',   'DATETIME',      ''),
        ]
    },
]

# ── Relaciones ────────────────────────────────────────────
RELATIONS = [
    # (source_table, target_table, label_source, label_target)
    ('sedes',           'cupos_sede',       '1', 'N'),
    ('sedes',           'personas',         '1', 'N'),
    ('sedes',           'solicitudes',      '1', 'N'),
    ('personas',        'solicitudes',      '1', 'N'),
    ('motivos_ramo',    'solicitudes',      '1', 'N'),
    ('estados_solicitud','solicitudes',     '1', 'N'),
]

# ── Generador XML ─────────────────────────────────────────
def escape(s):
    return s.replace('&','&amp;').replace('<','&lt;').replace('>','&gt;').replace('"','&quot;')

cells = []
cell_id = 2

def new_id():
    global cell_id
    c = cell_id
    cell_id += 1
    return str(c)

table_ids = {}   # table_name → mxCell id

for tbl in TABLES:
    h = table_height(tbl['fields'])
    c   = tbl['color']
    tid = new_id()
    table_ids[tbl['id']] = tid

    cells.append(
        f'    <mxCell id="{tid}" value="&lt;b&gt;{escape(tbl["name"])}&lt;/b&gt;" '
        f'style="swimlane;fontStyle=1;align=center;startSize={HH};'
        f'fillColor={c["fill"]};strokeColor={c["stroke"]};fontColor={c["font"]};'
        f'childLayout=stackLayout;horizontal=1;horizontalStack=0;'
        f'resizeParent=1;resizeParentMax=0;collapsible=0;marginBottom=0;'
        f'swimlaneLine=1;fontSize=13;" '
        f'vertex="1" parent="1">'
        f'\n      <mxGeometry x="{tbl["x"]}" y="{tbl["y"]}" width="{W}" height="{h}" as="geometry"/>'
        f'\n    </mxCell>'
    )

    for i, (fname, ftype, key) in enumerate(tbl['fields']):
        fid = new_id()
        y_off = HH + i * RH

        # styling based on key
        if key == 'PK':
            style = f'fontStyle=5;fontSize=11;'  # bold+underline
            label = f'🔑 {fname} : {ftype}'
        elif key.startswith('FK'):
            ref = key.replace('FK→','')
            style = f'fontStyle=2;fontSize=11;'  # italic
            label = f'⬡ {fname} : {ftype}'
        elif key == 'UK' or key == 'UK+':
            style = f'fontStyle=4;fontSize=11;'  # underline
            label = f'{fname} : {ftype} (UK)'
        else:
            style = f'fontStyle=0;fontSize=11;'
            label = f'{fname} : {ftype}'
            if key == 'NULL':
                label += ' NULL'

        cells.append(
            f'    <mxCell id="{fid}" value="{escape(label)}" '
            f'style="text;strokeColor=none;fillColor=none;align=left;'
            f'verticalAlign=middle;spacingLeft=8;spacingRight=4;'
            f'overflow=hidden;rotatable=0;{style}'
            f'points=[[0,0.5],[1,0.5]];portConstraint=eastwest;" '
            f'vertex="1" parent="{tid}">'
            f'\n      <mxGeometry y="{y_off}" width="{W}" height="{RH}" as="geometry"/>'
            f'\n    </mxCell>'
        )

# ── Edges ─────────────────────────────────────────────────
for src, dst, lbl_src, lbl_dst in RELATIONS:
    eid = new_id()
    cells.append(
        f'    <mxCell id="{eid}" value="" '
        f'style="edgeStyle=orthogonalEdgeStyle;endArrow=ERmany;startArrow=ERone;'
        f'exitX=1;exitY=0.5;exitDx=0;exitDy=0;entryX=0;entryY=0.5;entryDx=0;entryDy=0;'
        f'endFill=0;startFill=0;jettySize=auto;orthogonalLoop=1;" '
        f'edge="1" source="{table_ids[src]}" target="{table_ids[dst]}" parent="1">'
        f'\n      <mxGeometry relative="1" as="geometry"/>'
        f'\n    </mxCell>'
    )

# ── Armar XML final ───────────────────────────────────────
xml = '''<?xml version="1.0" encoding="UTF-8"?>
<mxGraphModel dx="1500" dy="900" grid="1" gridSize="10" guides="1"
  tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1"
  pageWidth="1169" pageHeight="827" math="0" shadow="0">
  <root>
    <mxCell id="0" />
    <mxCell id="1" parent="0" />
''' + '\n'.join(cells) + '''
  </root>
</mxGraphModel>
'''

out = r'c:\laragon\www\flores\docs\er_flores_db.drawio'
with open(out, 'w', encoding='utf-8') as f:
    f.write(xml)

print(f'ER diagram guardado en: {out}')
print(f'Total celdas generadas: {cell_id - 2}')
