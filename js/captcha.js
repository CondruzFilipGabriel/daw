document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("login-form");

    if (form) {
        form.addEventListener("submit", async (event) => {
            event.preventDefault();

            const token = await grecaptcha.execute(recaptchaSiteKey, { action: "login_form" });

            // Add token and action to the form
            const tokenField = document.createElement("input");
            tokenField.setAttribute("type", "hidden");
            tokenField.setAttribute("name", "g-recaptcha-response");
            tokenField.setAttribute("value", token);
            form.appendChild(tokenField);

            const actionField = document.createElement("input");
            actionField.setAttribute("type", "hidden");
            actionField.setAttribute("name", "g-recaptcha-action");
            actionField.setAttribute("value", "login_form");
            form.appendChild(actionField);

            form.submit();
        });
    }
});
