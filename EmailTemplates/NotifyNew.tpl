<style>
body { font-size: 10px; font-family: verdana; }
.t1 { padding:0; margin:0; border:1px solid #AAAAAA; border-collapse: collapse; }
.t1 th { color: black; font-size: 10px; font-family: verdana;  background-color: #DDDDDD;  }
.t1 td { text-align:left; font-size: 10px; font-family: verdana; background-color: white;   }
.title { font-weight: bold; text-align:left; font-size: 12px; font-family: Verdana; background-color: white; color: #330099}
</style>
<body>

<span class="title">
  The following new article has been added <br>
  </span>
<table class="t1" width="600px"  border="0" cellspacing="2" cellpadding="2">
  <tr>
    <th width="120" scope="row"><a title="Click to view Article" href="{SITE_URL}article.php?ID={ID}">{ID}</a></th>
    <td width="480"><b>{Title}</b></td>
  </tr>
  <tr>
    <th scope="row">Product</th>
    <td>{Product}</td>
  </tr>
  <tr>
    <th scope="row">Group</th>
    <td>{GroupName}</td>
  </tr>
  <tr>
    <th scope="row">Created</th>
    <td>{CREATED}</td>
  </tr>
  <tr>
    <th scope="row">Updated</th>
    <td>{LASTMODIFIED}</td>
  </tr>
</table>
<br><i>See your <a href="{SITE_URL}myprofile.php">profile</a> to disable or modify notifications.</i>


