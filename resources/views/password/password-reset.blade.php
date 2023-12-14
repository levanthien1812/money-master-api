<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Quicksand&display=swap" rel="stylesheet" />
    <title>Password reset link</title>
</head>

<body>

    <div style="padding: 32px 18%; background-color: #dedede; border-radius: 10px;">
        <table
            style="margin-left: auto; margin-right: auto; width: 100%; background-color: #ffffff; border-radius: 10px; padding: 32px 48px;">
            <thead>
                <tr>
                    <th>
                        <img src="{{ config('app.url') }}/images/logo-money-master.png" alt=""
                            style="width: 200px; height: 200px;">
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <p style="font-size: 30px; text-transform: uppercase;text-align: center;">Money Master</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p style="font-weight: bold; font-size: 18px">Hello!</p>
                        <p style="font-size: 18px; margin-bottom: 16px">Click this button to reset
                            password: {{ ' ' }}
                            <a style="padding: 6px 16px; font-size: 15px; color: #ffffff; background-color: #a023f4; font-weight: bold; border-radius: 4px; text-decoration: none;"
                                href="{{ $link }}">Reset password</a>
                        </p>

                        <div style="height: 1px; background-color: #c9c9c9; margin-bottom: 12px;"></div>

                        <p style="color: #8d8d8d; font-size: 13px;">If you have any trouble clicking reset password
                            button, copy and paste
                            the URL below into your web browser: {{ $link }}</p>
                    </td>
                </tr>
            </tbody>
        </table>


    </div>
</body>

</html>
