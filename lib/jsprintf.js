/***********************************************************************

   jsprintf in Javascript to substitute for C sprintf() function
    
  call:   jsprintf(format, ...);
************************************************************************/

var LEFT_JUSTIFY =      0x01,
    LEADING_ZEROES =    0x02,
    SIGNED =            0x04,
    EXPECT_PRECISION =  0x08,
    FORMAT_ERROR =      0x10,
    ARG_SUBBED =        0x20,
    EXPECT_CONVERSION = 0x40;

function isdigit(ch)
{
    if (ch < '0' || ch > '9')
        return (0);
    return (1);
}

function string_inside_field(str, field_width, option)
{
   var block, i;

   if (typeof(str) == "undefined" || typeof(field_width) ==
"undefined" ||
                typeof(option) == "undefined" || field_width <= 0)
       return (null);
   block = "";
   if (option & LEFT_JUSTIFY)
       block += str;
   for (i = field_width - str.length; i > 0; i--)
       block += " ";
   if ((option & LEFT_JUSTIFY) == 0)
       block += str;
   return (block);
}

function jsprintf(format)
{
   var i, state, idx, precision, fwidth, argn, val, buf, str, len,
          buflen, valstr;

   state = 0;
   argn = 1;
   if (typeof(format) == "undefined" || typeof(format) == "null" ||
                                  typeof(format) != "string")
       return (-1);
   // count args in format and those
   buf = "";
   while ((idx = format.search(/%/)) >= 0)
   {
       if (idx > 0)
       buf += format.slice(0, idx);
       idx++;
       do {
          switch (ch = format.charAt(idx))
          {
          case 'l':
          case 'h':
             if ((state & EXPECT_CONVERSION) != 0)
             {
                 state |= FORMAT_ERROR;
                 break;
             }
             break;
          case 'u':
             arguments[argn] &= 0x7ffffff;
          case 'd':
          case 'i':
             val = arguments[argn++];
             state |= ARG_SUBBED;
             idx++;
             if (typeof(val) == "undefined")
             {
                buf += " ";
                break;
             }
             val = parseInt(val.toString());
             if (isNaN(val) || val == Number.NEGATIVE_INFINITY ||
                  val == Number.POSITIVE_INFINITY)
             { 
                  buf += " ";
                  break;
             }
             valstr = new String(val);
             if (typeof(fwidth) == "undefined")
                 fwidth = valstr.length;
             buflen = valstr.length;
             if ((state & LEFT_JUSTIFY) == 0)
                 valstr = "";
             for (i = fwidth - buflen; i > 0; i--)
                if ((state & LEADING_ZEROES) == 0 || 
                               (state & LEFT_JUSTIFY) != 0)
                   valstr += " ";
                else
                   valstr += "0";
             if ((state & LEFT_JUSTIFY) == 0)
                valstr += val;
             buf += valstr;
             break;
          case 'x':
          case 'X':
             hexval = parseInt(arguments[argn++].toString());
             hexadecimal = new String(2 *
                    (Math.log(hexval)/Math.log(10) + 1) * 
                     Math.log(10) / Math.log(16));
             i = 0;
             while (hexval > Math.pow(16, i))
                 i++;
             i--;
             while (i >= 0)
                 ;
             if (hexadecimal.length < fwidth)
                format_integer(hexadecimal, state, fwidth);
             delete hexadecimal;
             break;
          case 'p': /* pointers not allowed */
             break;
          case 'o':
             break;
          case 'e':
          case 'E':
          case 'f':  /* default is [-]ddd.ddd or %6.3f */
             idx++;
             state |= ARG_SUBBED;
             if (typeof(precision) == "undefined")
                precision = 3;
             if (typeof(fwidth) == "undefined")
                fwidth = 6;
             if (precision > fwidth)
                fwidth = precision;
             val = arguments[argn++];
             if (typeof(val) == "undefined")
             {
                buf += " ";
                break;
             }
             val = parseFloat(val.toString());
             if (isNaN(val) || val == Number.NEGATIVE_INFINITY ||
                    val == Number.POSITIVE_INFINITY)
             {
                buf += " ";
                break;
             }
             valstr = new String(val);
             if ((i = valstr.indexOf(".")) < 0)
             {
                 valstr += ".";
                 for (i = 0; i < precision ; i++)
                    valstr += "0";
             }
             else if (valstr.length - i > precision)
             {
                 delete valstr;
                 val = Math.round(val * Math.pow(10, precision))
                          / Math.pow(10, precision);
                 valstr = new String(parseFloat(val.toString()));
                 if ((i = valstr.indexOf(".")) < 0)
                 {
                     i = 0;
                     valstr += ".";
                 }
                 else
                 {
                     valstr = valstr.substring(0, i + precision + 1);
                     i = valstr.length - i - 1;
                 }
                 while (i++ < precision)
                    valstr += "0";
             }
             if ((state & SIGNED) != 0 && val > 0.0)
                valstr = "+" + valstr;
             if (fwidth > valstr.length)
                if ((state & LEFT_JUSTIFY) != 0)
             for (i = fwidth - valstr.length; i > 0; i--)
                valstr += " ";
             else if ((state & LEADING_ZEROES) != 0)
                for (i = fwidth - valstr.length; i > 0; i--)
                    valstr = "0" + valstr;
             else
                for (i = fwidth - valstr.length; i > 0; i--)
                    valstr = " " + valstr;
             buf += valstr;
             break;
          case 'g':
          case 'G':
             break;
          case '-':
             if ((state & EXPECT_CONVERSION) != 0)
             {
                 state |= FORMAT_ERROR;
                 break;
             }
             if ((state & EXPECT_PRECISION) == 0)
                 state |= LEFT_JUSTIFY;
             idx++;
             break;
          case '*':
             if ((state & EXPECT_CONVERSION) != 0)
             {
                state |= FORMAT_ERROR;
                break;
             }
             if ((state & EXPECT_PRECISION) == 0)
                fwidth = parseInt(arguments[argn++].toString());
             else
                precision = parseInt(arguments[argn++].toString());
             break;
          case '+':
             if ((state & EXPECT_CONVERSION) != 0)
             {
                state |= FORMAT_ERROR;
                break;
             }
             if ((state & EXPECT_PRECISION) == 0)
                state |= SIGNED;
             idx++;
             break;
          case '.':
             if ((state & EXPECT_CONVERSION) != 0)
             {
                state |= FORMAT_ERROR;
                break;
             }
             if ((state & EXPECT_PRECISION) != 0)
                state |= FORMAT_ERROR;
             else
                state |= EXPECT_PRECISION;
             idx++;
             break;
          case ' ': /* if there is a sign, this is ignored */
             if ((state & EXPECT_CONVERSION) != 0)
             {
                 state |= FORMAT_ERROR;
                 break;
             }
             if ((state & SIGNED) == 0)
                 break;
             break;
          case '#':
             break;
          case '%':
             if ((state & EXPECT_CONVERSION) != 0)
             {
                 state |= FORMAT_ERROR;
                 break;
             }
             buf += "%";
             format = format.substr(idx);
             state |= ARG_SUBBED;
             break;
          case 's':
             state |= ARG_SUBBED;
             idx++;
             str = arguments[argn++];
             if (typeof(str) == "undefined")
             {
                buf += " ";
                break;
             }
             if (typeof(str) != "string")
                str = str.valueOf().toString();
             if (typeof(precision) == "undefined" || 
                                precision == null)
             {
                if (typeof(fwidth) == "undefined" || fwidth == null)
                    buf += str;
                else /* fwidth specifies a minimum width */
                    buf += string_inside_field(str, fwidth, state);
             }
             else if (typeof(fwidth) == "undefined" || fwidth == null)
             {
                /* precision specifies maximum! */
                if (precision < str.length)
                   buf += str.slice(0, precision);
                else
                   buf += str;
             }
             else
             {
        /* precision specifies maximum and overrides fwidth value! */
                if (precision < fwidth)
                   if (precision < str.length)
                      buf += str.slice(0, precision);
                   else
                      buf += string_inside_field(str, fwidth, state);
                else
                   if (precision < str.length)
                      buf += str.slice(0, precision);
                   else if (fwidth < str.length)
                      buf += string_inside_field(str, fwidth, state);
                   else
                      buf += str;
              }
              break;
           case 'c':
              break;
           case '0':
              if ((state & EXPECT_CONVERSION) != 0)
              {
                  state |= FORMAT_ERROR;
                  break;
              }
              if ((state & EXPECT_PRECISION) == 0)
                 state |= LEADING_ZEROES;
              break;
           default:
              if ((state & EXPECT_CONVERSION) != 0)
              {
                  state |= FORMAT_ERROR;
                  break;
              }
              i = val = 0;
              while (isdigit(ch = format.charAt(idx)) == true)
              {
                 val = Number(ch) + 10 * val;
                 idx++;
              }
              if (state & EXPECT_PRECISION)
              {
                 precision = val;
                 state |= EXPECT_CONVERSION;
              }
              else
                 fwidth = val;
          }
       } while ((state & (ARG_SUBBED | FORMAT_ERROR)) == 0);
       state = 0;
       format = format.substr(idx);
       precision = fwidth = null;
    }
    buf += format;
    return (buf);
}
