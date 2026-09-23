    function checkinput(obj,e){
      var key;
      var isCtrl = false;
      var keychar;
      var reg;
      
      if(window.event) {
        key = e.keyCode;
        isCtrl = window.event.ctrlKey
      }
      else if(e.which) {
        key = e.which;
        isCtrl = e.ctrlKey;
      }
      if(key == 46){
        nextTab(obj,obj.value,e)
      } 
      if (key == 9 || key == 8 || key >= 48 && key <= 57 || isCtrl)
      {
        return true;
      }
      else
        return false;   
    }