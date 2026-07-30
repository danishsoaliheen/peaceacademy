<!DOCTYPE html>
<html>

<head>

    <title>Peace Academy ERP</title>

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:Arial, sans-serif;
            background:#f4f6f9;
        }

        .wrapper{
            display:flex;
            min-height:100vh;
        }

        /*
        |--------------------------------------------------------------------------
        | Sidebar
        |--------------------------------------------------------------------------
        */

        .sidebar{
            width:250px;
            background:#1e293b;
            color:white;
            padding:20px;
        }

        .sidebar h2{
            margin-bottom:30px;
            font-size:22px;
            text-align:center;
        }

        .sidebar a{
            display:block;
            color:white;
            text-decoration:none;
            padding:12px 15px;
            margin-bottom:10px;
            border-radius:5px;
            transition:0.3s;
        }

        .sidebar a:hover{
            background:#334155;
        }

        /*
        |--------------------------------------------------------------------------
        | Main Content
        |--------------------------------------------------------------------------
        */

        .main{
            flex:1;
            padding:20px;
        }

        .topbar{
            background:white;
            padding:15px 20px;
            margin-bottom:20px;
            border-radius:8px;
            box-shadow:0 2px 5px rgba(0,0,0,0.1);
        }

        .content{
            background:white;
            padding:20px;
            border-radius:8px;
            box-shadow:0 2px 5px rgba(0,0,0,0.1);
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        table th,
        table td{
            border:1px solid #ddd;
            padding:10px;
        }

        table th{
            background:#f1f5f9;
        }

        .btn{
            display:inline-block;
            background:#0d6efd;
            color:white;
            padding:10px 15px;
            text-decoration:none;
            border:none;
            border-radius:5px;
            cursor:pointer;
        }

        .btn:hover{
            opacity:0.9;
        }

    </style>

</head>

<body>

<div class="wrapper">

    <!-- Sidebar -->

    <div class="sidebar">

        <h2>
            Peace Academy
        </h2>

        <a href="/">
            Dashboard
        </a>

        <a href="/students">
            Students
        </a>

        <a href="/enrollments">
            Enrollments
        </a>

        <a href="/fee-vouchers">
            Fee Vouchers
        </a>

        <a href="/monthly-fee-generator">
            Monthly Fee Generator
        </a>

        <a href="/class-fee-structures">
            Class Fee Structure
        </a>

    </div>

    <!-- Main -->

    <div class="main">

        <div class="topbar">

            <h2>
                Peace Academy ERP System
            </h2>

        </div>

        <div class="content">

            @yield('content')

        </div>

    </div>

</div>

</body>
</html>