<style type="text/css">
body { font-size: 10px; font-family: verdana; }
.t1 { padding:0; margin:0; border:1px solid #AA2222; border-collapse: collapse; }
.t1 th { color: black; font-size: 10px; font-family: verdana;  background-color: #DDDDDD;  }
.t1 td { text-align:left; font-size: 10px; font-family: verdana; background-color: white;   }
.title { font-weight: bold; text-align:center; font-size: 12px; font-family: Verdana; background-color: white; color: #330099}
</style>
<body>

<span class="title">
A note has been Added to an Article that you have previously Created, Modified,Approved or are listed as a contact for.<br>
  </span>
<table class="t1" width="650px"  border="0" cellspacing="2" cellpadding="2">
  <tr>
    <th width="100">Article</th>
    <td width="550"><b><a title="Click to view" href="{SITE_URL}admin_article.php?ID={ID}">{Title}</a></b></td>
  </tr>
  <tr>
    <th >Note Type</th>
    <td>{NoteType}</td>
  </tr>
  <tr>
    <th valign="top">Note</th>
    <td>{Notes}</td>
  </tr>
</table>
<br><i>See your <a href="{SITE_URL}myprofile.php">profile</a> to disable or modify notifications.</i>
