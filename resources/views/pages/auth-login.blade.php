<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap</title>
</head>
<body>

<h2>Giriş Yap</h2>

<form id="loginForm">
    <input type="email" id="email" placeholder="E-posta" required>
    <br><br>
    <input type="password" id="password" placeholder="Şifre" required>
    <br><br>
    <button type="submit">Giriş Yap</button>
</form>

<p id="error" style="color:red;"></p>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorBox = document.getElementById('error');
    errorBox.innerText = "";

    try {
        const response = await fetch('http://localhost:8000/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();

        if (!response.ok) {
            errorBox.innerText = data.message || "Giriş başarısız";
            return;
        }


    
        //localStorage.setItem('token', data.token.access_token);

        
        //localStorage.setItem('user', JSON.stringify(data.user));

        
        //window.location.href = "/dashboard";

    } catch (error) {
        errorBox.innerText = "Sunucu hatası oluştu.";
    }
});
</script>

</body>
</html>
