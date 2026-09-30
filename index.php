<html lang="en">
<head>
    <link rel="stylesheet" href="uikit-core.rtl.css">
    <title>secure e2e messenger</title>
</head>
<body>
<div id="container" class="uk-container uk-container-center">

</div>
<div class="uk-container uk-width-1-1 uk-card-default uk-border-rounded uk-float-right"
     style="background: #EBEBEB; position: fixed; bottom: 0"
>
    <form id="send-box" style="display: flex;">
        <input class="uk-input uk-width-large" name="message" id="message" type="text" placeholder="Type Message ...">
        <button id="btn-send" class="uk-float-right uk-width-1-5 uk-button-primary uk-padding-small">
            Send!
        </button>
    </form>
</div>
<div class="initial-form">
    <form id="init" class="uk-form-width-large">
        <div>
            <label class="label-form">
                username:
                <input class="uk-input uk-form-width-medium" name="username" id="username" type="text">
            </label>
        </div>
        <br>
        <div>
            <label>
                partner:
                <input class="uk-input uk-form-width-medium " name="partner" id="partner" type="text">
            </label>
        </div>
        <br>

        <div>
            <label>
                password:
                <input class="uk-input uk-form-width-medium " name="password" id="password" type="password">
            </label>
        </div>
        <br>

        <div>
            <label>
                cipher key:
                <input class="uk-input uk-form-width-medium " name="key" id="key" type="password">
            </label>
        </div>
        <br>

        <button type="button" class="uk-button uk-button-danger" id="btn-submit">Submit!</button>
    </form>
</div>
</body>
</html>
<style>
    .message {
        padding: 5px;
        display: grid;
        background-color: bisque;
    }

    .initial-form {
        display: flex;
        text-align: end;
        padding-top: 25%;
        padding-left: 20%;
        margin: 20px;
    }

    .input-form {
        margin-left: 10px;
    }
</style>
<script src="./jquery.min.js" type="text/javascript"></script>
<script src="./jquery.validate.min.js" type="text/javascript"></script>

<script>
    function reloadMessages() {
        console.log("reloaded!")
        let key = document.getElementById('key').value
        let user = document.getElementById('username').value
        let partner = document.getElementById('partner').value
        let pass = document.getElementById('password').value
        $.ajax({
            url: 'http://localhost:8080/api/message/list',
            type: 'GET',
            data: {
                username: user,
                password: pass,
                partner: partner,
            },
            success: function (response) {
                $('.initial-form').css('display', 'none');
                let html = `
                            <div class="uk-inline uk-width-1-1 uk-grid-margin uk-padding-small">
                                <table class="uk-table uk-table-hover uk-text-center uk-table-small uk-table-divider uk-table-middle uk-table-striped">
                                    <tbody>
                        `;

                $.each(response.data, function (date, data) {
                    const decryptedMessage = decrypt(data.message, key);
                    var floatAlign = 'left'
                    if (data.sender === user) {
                        floatAlign = 'right'
                    }
                    html += `
                                        <tr>
                                            <td class="uk-float-${floatAlign}">
                                            <span style="font-size: small">${data.date}:</span>
                                            <p>${decryptedMessage}</p> </td>
                                        </tr>
                            `;
                });
                $('#container').html(html)
                $('#send-box').css('display', 'flex')
            },
            error: function (jqXHR, textStatus, errorThrown) {
            },
            complete: function () {
            }
        });
    }

    function encrypt(message, key) {
        const result = []
        message.split("").map(function (value) {
            let ascii = value.charCodeAt(0)
            key *= 1
            result.push(String.fromCharCode(ascii + key))
        });
        return result.join("");
    }

    function decrypt(message, key) {
        const result = []
        message.split("").map(function (value) {
            let ascii = value.charCodeAt(0)
            result.push(String.fromCharCode(ascii - key))
        });
        return result.join("");
    }


    var printButton = document.getElementById('btn-submit');
    var sendButton = document.getElementById('btn-send');

    sendButton.addEventListener('click', function (e) {
        e.preventDefault();
        let key = document.getElementById('key').value
        let user = document.getElementById('username').value
        let partner = document.getElementById('partner').value
        let pass = document.getElementById('password').value
        let message = document.getElementById('message').value
        const encryptedMessage = encrypt(message, key.valueOf())
        var payload = {
            message: encryptedMessage,
            sender: user,
            receiver: partner,
            password: pass
        }
        $.ajax({
            url: 'http://localhost:8080/api/message/send',
            type: 'POST',
            data: JSON.stringify(payload),
            processData: false,
            contentType: 'application/json',
            credentials: false,
            success: function (response) {
                reloadMessages();
            }
        })
    })

    printButton.addEventListener('click', function (e) {
        e.preventDefault();
        let key = document.getElementById('key').value
        let user = document.getElementById('username').value
        let partner = document.getElementById('partner').value
        let pass = document.getElementById('password').value
        $.ajax({
            url: 'http://localhost:8080/api/message/list',
            type: 'GET',
            data: {
                username: user,
                password: pass,
                partner: partner,
            },
            success: function (response) {
                $('.initial-form').css('display', 'none');
                let html = `
                            <div class="uk-inline uk-width-1-1 uk-grid-margin uk-padding-small">
                                <table class="uk-table uk-table-hover uk-text-center uk-table-small uk-table-divider uk-table-middle uk-table-striped">
                                    <tbody>
                        `;

                $.each(response.data, function (date, data) {
                    const decryptedMessage = decrypt(data.message, key);
                    var floatAlign = 'left'
                    if (data.sender === user) {
                        floatAlign = 'right'
                    }
                    html += `
                                        <tr>
                                            <td class="uk-float-${floatAlign}">
                                            <span style="font-size: small">${data.date}:</span>
                                            <p>${decryptedMessage}</p> </td>
                                        </tr>
                            `;
                });
                $('#container').html(html)
                $('#send-box').css('display', 'flex')

                window.setInterval(function () {
                    reloadMessages();
                }, 3000)
            },
            error: function (jqXHR, textStatus, errorThrown) {
            },
            complete: function () {
            }
        });


    });


</script>



