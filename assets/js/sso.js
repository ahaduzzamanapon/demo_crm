// Requires crypto-js library: https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.2.0/crypto-js.min.js

function ssoLogin(ssoUrl, userData, sharedSecret) {
    const data = { ...userData };

    // Create a query string from the user data
    const queryString = Object.keys(data).map(key => key + '=' + data[key]).join('&');

    // Generate the hash
    const hash = CryptoJS.HmacSHA256(queryString, sharedSecret).toString(CryptoJS.enc.Hex);
    data.hash = hash;

    fetch(ssoUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/dashboard';
        } else {
            alert(data.message);
        }
    })
    .catch((error) => {
        console.error('Error:', error);
    });
}
