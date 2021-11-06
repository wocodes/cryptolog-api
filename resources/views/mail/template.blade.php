<html>
    <style>
        body {
            width:30%;
            margin: 0 auto;
        }

        table {
            width: 50%;
            box-sizing:border-box;
            font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
            margin:0 auto;
            padding:0;
        }

        h1 {
            color:#3d4852;font-size:12px;font-weight:bold;margin-top:0;text-align:left
        }
        
        p {
            font-size:16px;line-height:1.5em;margin-top:0;text-align:left
        }
        
        td {
            box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol'
        }
    </style>

    <body>
        <table role="presentation">
            <tbody>
                <tr>
                    <td style="padding:25px 0;text-align:center;background:#0f5cbd;color:#fff;">
                        <a href="https://s7818407.smtp02.pulse-stat.com/sl/aba7f558d945fffc3c149da8b095bc570/"
                           style="color:#fff;font-size:19px;font-weight:bold;text-decoration:none;display:inline-block"
                           target="_blank"
                           data-saferedirecturl="https://www.google.com/url?q=https://s7818407.smtp02.pulse-stat.com/sl/aba7f558d945fffc3c149da8b095bc570/&amp;source=gmail&amp;ust=1636306065326000&amp;usg=AFQjCNF18FcHCABEyftgQo8jfFWMAGpJEw">
                            Assetlog
                        </a>
                    </td>
                </tr>

                <tr>
                    <td style="max-width:100vw;padding:32px">
                        <h1>Hi {{ $nameFromEmail }}</h1>

                        @foreach($contents as $paragraph)
                            <p>{!! $paragraph !!}</p>
                        @endforeach


                        <p>With &#10084;&#65039; from AssetLog!</p>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="max-width:100vw;padding:32px">
                        <p style="line-height:1.5em;margin-top:0;color:#b0adc5;font-size:12px;text-align:center">
                            © {{ date('Y') }} Assetlog. All rights reserved.
                        </p>
                    </td>
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
</html>