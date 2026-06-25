<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<style>

body{

font-family: dejavusans;

font-size:12px;

}

table{

width:100%;

border-collapse:collapse;

margin-top:20px;

}

table,th,td{

border:1px solid black;

}

th{

background:#ececec;

padding:8px;

}

td{

padding:6px;

text-align:center;

}

.title{

text-align:center;

font-size:20px;

font-weight:bold;

margin-bottom:20px;

}

.company{

text-align:center;

margin-bottom:10px;

}

</style>

</head>

<body>

<div class="company">

<h2>{{ $company->company_name_en }}</h2>

</div>

<div class="title">

Audit Report

</div>

<table>

<thead>

<tr>

<th>#</th>

<th>Username</th>

<th>Operation</th>

<th>Date & Time</th>

</tr>

</thead>

<tbody>

@forelse($logs as $log)

<tr>

<td>{{ $loop->iteration }}</td>

<td>{{ $log->username }}</td>

<td>{{ $log->operation }}</td>

<td>{{ $log->performed_at }}</td>

</tr>

@empty

<tr>

<td colspan="4">

No Data

</td>

</tr>

@endforelse

</tbody>

</table>

</body>

</html>