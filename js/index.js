// Wait for the DOM to fully load
document.addEventListener("DOMContentLoaded", () => {
    // Select all forms in the event container
    const forms = document.querySelectorAll(".event-article");

    forms.forEach(form => {
        // Add an event listener for the submit event
        form.addEventListener("submit", event => {
            // Get the number_of_seats input from the form
            const seatInput = form.querySelector("input[name='number_of_seats']");
            const numberOfSeats = parseInt(seatInput.value, 10);

            // If the number of seats is 0, prevent form submission
            if (numberOfSeats === 0) {
                event.preventDefault(); // Prevent the form from submitting
                alert("Numarul de locuri rezervate trebuie sa fie cel putin 1.");
            }
        });
    });
});