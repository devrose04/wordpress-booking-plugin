function displayCount() {
    const guestsPopup = document.getElementById('guests-popup');
    if (guestsPopup) {
        guestsPopup.style.display = 'block';
    } else {
        console.error('Guests popup element not found');
    }
}

function popup() {
    const guestsPopup = document.getElementById('guests-popup');
    if (guestsPopup) {
        guestsPopup.style.display = 'none';
    } else {
        console.error('Guests popup element not found');
    }
    document.getElementById('guests').value =
        document.getElementById('accommodates').value + " Guests | " +
        document.getElementById('bedrooms').value + " Bedrooms";
}

function updateValue(id, increment) {
    const input = document.getElementById(id);
    if (input) {
        let value = parseInt(input.value);
        value = isNaN(value) ? 0 : value;
        value += increment;
        if (value < 1) value = 1;
        console.log(id, value);
        input.value = value;
    } else {
        console.error('Input element not found:', id);
    }
}

// Close the popup if the user clicks outside of it
document.addEventListener('click', function(event) {
    console.log(123);
    const guestsPopup = document.getElementById('guests-popup');
    const guestsInput = document.getElementById('guests');
    const isClickInside = guestsPopup && guestsPopup.contains(event.target) || guestsInput && event.target === guestsInput;
    if (!isClickInside) {
        if (guestsPopup) {
            guestsPopup.style.display = 'none';
        } else {
            console.error('Guests popup element not found');
        }
    }
});