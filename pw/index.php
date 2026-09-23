<?php session_start();
 session_destroy();
//header('Location: http://www.warehouse.philkonstrak.com');
?>
Redirecting to <strong>www.warehouse.philkonstrak.com</strong>....... Please Wait!
<script>
   setTimeout("sitePage()",1000);
  function sitePage(){
    window.location="http://www.warehouse.philkonstrak.com";
  }
</script>