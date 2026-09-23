<?php
function print_header($title){
echo  '
    <style type="text/css">
    #hName{font-size: 15px; font-family: Tahoma;font-weight: bolder;}
    #hAddress{font-size: 10px; font-family: Tahoma; line-height: 15px;}
    #DTitle{font-size: 15px; font-family: Tahoma;}
    </style>
<table width="700" border="1" align="center">
  <tr style="border: 1px solid #000;">
    <td align="center">
        <table width="100%" border="0" align="center">
          <tr>
            <td width="160" align="right"><img src="../img/header-logo.jpg" width="125" height="153"></td>
            <td align="center">
                <div id="hName">PHILKONSTRAK DEVELOPMENT CORPORATION</div>
                <div id="hAddress">
                    <div>Design &bull; Estimate &bull; Construct &bull; Develop</div>
                    <div>Door 3 MGR Building, Apitong Street, Sunrise Village Extension Bulacao, Cebu City</div>
                    <div>Tel# (Main Office) (032) 407-3213; Cel# (Main Office) 0923-304-3498; 0905-296-7376</div>
                    <div>Tel# (Bohol Coordinating Office) (038) 411-2753</div>
                    <div>E-mail Add: <a href="#">philkonstrak@hotmail.com</a>; <a href="#">philkonstrakdevtcorp@gmail.com</a></div>
                    <div>Website: www.philkonstrak.com</div>
                </div>
            </td>
            <td width="160" align="left"><img src="../img/pdc-iso.gif" width="125" height="153"></td>
          </tr>
        </table>
    </td>
  </tr>
  <tr style="border: 1px solid #000;">
    <td align="center"><div id="DTitle"><strong>'.$title.'</strong></div></td>
  </tr>
</table>
';
}
?>