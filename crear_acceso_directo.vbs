Option Explicit

Dim WshShell, oShortcut
Dim strDir, strBat, strIcon, strAcceso

Set WshShell = CreateObject("WScript.Shell")

' Directorio donde esta este script (la raiz del proyecto)
strDir  = Left(WScript.ScriptFullName, InStrRev(WScript.ScriptFullName, "\"))
strBat  = strDir & "iniciar.bat"
strIcon = strDir & "public\img\petalo.ico"

' Destino: Escritorio del usuario actual
strAcceso = WshShell.SpecialFolders("Desktop") & "\Petalo.lnk"

' Verificar que el archivo bat existe
Dim fso
Set fso = CreateObject("Scripting.FileSystemObject")
If Not fso.FileExists(strBat) Then
    MsgBox "No se encontro iniciar.bat en:" & vbCrLf & strBat, vbCritical, "Petalo"
    WScript.Quit 1
End If

' Usar icono personalizado si existe, de lo contrario usar uno de Windows
If Not fso.FileExists(strIcon) Then
    strIcon = "shell32.dll,24"
End If

Set oShortcut = WshShell.CreateShortcut(strAcceso)
oShortcut.TargetPath       = strBat
oShortcut.WorkingDirectory = strDir
oShortcut.WindowStyle      = 7
oShortcut.IconLocation     = strIcon
oShortcut.Description      = "Sistema Petalo - Solicitud de Bockets"
oShortcut.Save

Set oShortcut = Nothing
Set fso       = Nothing
Set WshShell  = Nothing

MsgBox "Acceso directo 'Petalo' creado en el Escritorio." & vbCrLf & vbCrLf _
     & "Para usar un icono personalizado, coloca el archivo" & vbCrLf _
     & "petalo.ico en la carpeta public\img\ y ejecuta" & vbCrLf _
     & "este script de nuevo.", vbInformation, "Petalo"
