<style>
body { font-size: 10px; font-family: verdana; }
.t1 { padding:0; margin:0; border:1px solid red; border-collapse: collapse; }
.t1 th { color: black; font-size: 10px; font-family: verdana;  background-color: #DDDDDD;  }
.t1 td { text-align:left; font-size: 10px; font-family: verdana; background-color: white;   }
.title { font-weight: bold; text-align:center; font-size: 12px; font-family: Verdana; background-color: white; color: #330099}
.msg { font-weight: bold; color: #333;}
</style>
<body>  

<span class="title">
  Bulletin Message <br>
</span>

<table class="t1" width="650px"  border="0" cellspacing="2" cellpadding="2">
  <tr>
    <th>Subject</th>
    <td width="530"><b><a title="Click to view Bulletin" href="{SITE_URL}message.php?ID={ID}">{Subject}</a></b></td>
  </tr>
  <tr>
    <th>Type</th>
    <td>{Type}</td>
  </tr>
  <tr>
    <th>Service Type </th>
    <td>{ServiceType}</td>
  </tr>
  <tr>
    <th>Service Name </th>
    <td>{ServiceName}</td>
  </tr>
  <tr>
    <th>Group</th>
    <td>{GroupName}</td>
  </tr>
  <tr>
    <th>Ticket Number</th>
    <td>{TicketNumber}</td>
  </tr>
  <tr>
    <th>Start Time </th>
    <td>{StartTime}</td>
  </tr>
  <tr>
    <th>End Time </th>
    <td>{EndTime}</td>
  </tr>
  <tr>
    <th>Escalated</th>
    <td>{Escalated}</td>
  </tr>
  <tr>
    <th colspan="2"><em>Message</em></th>
  </tr>
  <tr>
    <td colspan="2" class="msg">{Message}</td>
  </tr>
</table>
<br><i>See your <a href="{SITE_URL}myprofile.php">profile</a> to disable or modify notifications.</i>

