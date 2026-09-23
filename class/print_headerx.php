<?php
function print_header($title){
echo  '
    <style type="text/css">
    #hName{font-size: 15px; font-family: Tahoma;font-weight: bolder;}
    #hAddress{font-size: 10px; font-family: Tahoma; line-height: 15px;}
    #DTitle{font-size: 15px; font-family: Tahoma;}
    </style>
<table width="700" border="1" align="center">
  <tr>
    <td width="130" rowspan="2" align="center"><img src="../img/header-logo.jpg" width="125" height="153"></td>
    <td align="center">
        <div id="hName">PHILKONSTRAK DEVELOPMENT CORPORATION</div>
        <div id="hAddress">
            <div>Door 3 MGR Building, Apitong Street, Sunrise Village Extension Pardo, Cebu City</div>
            <div>Tel / Fax # (Main Office) (032) 236-0992; 412-9907; Cell# 0933-453-3109; 0932-904-2475</div>
            <div>(Bohol Coordinating Office) (038) 509- 9204; 544-0271 Cell# 0917-303-5874</div>
            <div>E-mail Add: <a href="#">philkonstrak@hotmail.com</a>; <a href="#">philkonstrakdevtcorp@gmail.com</a></div>
            <div>Website: www.philkonstrak.com</div>
        </div>
    </td>
  </tr>
  <tr>
    <td align="center"><div id="DTitle"><strong>'.$title.'</strong></div></td>
  </tr>
</table>
';
}
?>