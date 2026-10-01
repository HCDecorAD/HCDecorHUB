# Packaging

Staging remains runnable with Python/Tkinter and HC_AutoChat.bat.

54_CHECK_PYINSTALLER.bat detects free PyInstaller.
55_BUILD_EXE.bat builds a windowed executable only when PyInstaller is already available. Missing PyInstaller is reported, not silently installed.

Executable build does not promote CP5 by itself. Portable/EXE smoke evidence is still required on HOCUONG.
