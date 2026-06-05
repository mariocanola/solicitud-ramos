# -*- coding: utf-8 -*-
"""
Script principal: ejecutar con  python build_docx.py
Genera: documentacion_tecnica.docx
"""
exec(open('gen_docx.py',    encoding='utf-8').read())
exec(open('gen_content.py', encoding='utf-8').read())
exec(open('gen_content2.py',encoding='utf-8').read())

out = r'c:\laragon\www\flores\docs\documentacion_tecnica.docx'
doc.save(out)
print(f'\nDocumento guardado en: {out}')
