from pathlib import Path

p = Path(r"c:/xampp/htdocs/Local_Farm_Market/admin/users.php")
text = p.read_text(encoding="utf-8")
text = text.replace('<span class="badge text-bg-success">ADMIN</span>', '<span class="badge text-bg-success">SUPER ADMIN</span>')
text = text.replace('<span class="badge text-bg-primary">USER</span>', '<span class="badge text-bg-primary">FARMER</span>')
text = text.replace('<span class="badge text-bg-primary">Seller</span>', '<span class="badge text-bg-info">CUSTOMER</span>')
text = text.replace('<span class="badge text-bg-info">ACTIVE</span>', '<span class="badge text-bg-success">APPROVED</span>')
p.write_text(text, encoding="utf-8")
print("updated users.php")
