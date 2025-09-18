<!DOCTYPE html>
<html>

<head>
    <title>Beta Test</title>
</head>
<style>
    table,
    tr,
    td,
    th {
        border: 0;
    }
    body{
        font-family: 'Poppins', sans-serif;
    }

    @media only screen and (max-width:767px) {
        table {
            width: 100% !important;
        }
        td{
            padding: 0 15px;
        }
    }
</style>

<body width="100%" style="margin: 0; padding: 0 !important; color: black;">
    <div style="max-width:600px;margin:0 auto;width:100%;">
        <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="margin:auto;max-width:100%;">
            <tbody>
                <tr>
                    <td class="header" style="padding:20px 0; ">
                        <table align="center">
                            <tbody>
                                <tr>
                                    <td align="center">
                                        <img src="{{ asset('assets/img/box-socials-tm.png') }}" alt="Box Socials Logo" style="display:block; margin:0 auto; width:100px; max-width:100%; height:auto;">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td>
                        <p style="margin:0;">Hello,</p>
                    </td>
                </tr>

                <tr>
                    <td>
                        <p>A New User has joined Beta Waiting List!</p>
                    </td>
                </tr>

                <tr>
                    <td>
                        <p style="margin:0;"><strong>Name:</strong> {{ $name }}</p>
                    </td>
                </tr>

                <tr>
                    <td>
                        <p style="margin-top:0;"><strong>Email:</strong> {{ $email }}</p>
                    </td>
                </tr>

                <tr>
                    <td>
                        <p style="font-weight: bold;margin:0;">Best regards,</p>
                        <p style="font-weight: bold;margin-top:0;">Box Socials</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>