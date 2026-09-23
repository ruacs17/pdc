      function FormatCurrency(objNum)
      {
            var num = objNum.value;
            var ent, dec;
            if (num != '' && num != objNum.oldvalue)
            {
                  num = MoneyToNumber(num);
                  if (isNaN(num))
                  {
                        objNum.value = objNum.oldvalue;
                  }
                  else
                  {
                        var ev = (navigator.appName.indexOf('Netscape') != -1)?Event:event;
                        if (ev.keyCode == 190 || !isNaN(num.split('.')[1]))
                        {
                              objNum.value = AddCommas(num.split('.')[0])+'.'+num.split('.')[1];
                        }
                        else
                        {
                              objNum.value = AddCommas(num.split('.')[0]);
                        }
                        objNum.oldvalue = objNum.value;
                  }
            }
      }
      
      function MoneyToNumber(num)
      {
            return (num.replace(/,/g, ''));
            
      }
      
      function AddCommas(num)
      {
            numArr=new String(num).split('').reverse();
            for (i=3;i<numArr.length;i+=3)
            {
                  numArr[i]+=',';
            }
            return numArr.reverse().join('');
      }