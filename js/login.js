document.getElementById("loginForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const nickname = document.getElementById("nickname").value.trim();
    const hashpass = document.getElementById("hashpass").value.trim();

    if (!nickname || !hashpass) {
        alert("Por favor completa todos los campos.");
        return;
    }

    const formData = new FormData();
    formData.append("nickname", nickname);
    formData.append("hashpass", hashpass);

    try {
        const res = await fetch("login_process.php", {
            method: "POST",
            body: formData,
        });

        const data = await res.json();

        if (data.status === "success") {
            window.location.href = data.redirect;
        } else {
            UTIL.mostrarMensajeError(data.message || "Error de inicio de sesión.");
        }
    } catch (err) {
        UTIL.mostrarMensajeError( "Error de conexión con el servidor.");
        console.error(err);
    }
});
