import re
file_path = r"d:\program file\Project Kantor\e-arsip\resources\views\pages\laporan-spn\print.blade.php"

with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

# Replace both variants of the project name
content = re.sub(r"Pek\.? ?Perbaikan Bangunan Jaga Pintu Air Marina", r"{{ $payment->keperluan ?? '...' }}", content)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Replacement successful!")
