<!DOCTYPE html>
<html>

<head>

    <title>Peace Academy ERP</title>

    <meta charset="utf-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/theme.css') }}">

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
            width:260px;
            background:#1e293b;
            color:white;
            padding:20px 0;
        }

        .sidebar h2{
            text-align:center;
            margin-bottom:30px;
            font-size:22px;
        }

        .sidebar a{
            display:block;
            color:white;
            text-decoration:none;
            padding:12px 20px;
            transition:0.3s;
        }

        .sidebar a:hover{
            background:#334155;
        }

        .sidebar .menu-title{
            padding:10px 20px;
            font-size:13px;
            color:#94a3b8;
            margin-top:15px;
            text-transform:uppercase;
        }

        /*
        |--------------------------------------------------------------------------
        | Main Content
        |--------------------------------------------------------------------------
        */

        .main{
            flex:1;
            display:flex;
            flex-direction:column;
        }

        /*
        |--------------------------------------------------------------------------
        | Navbar
        |--------------------------------------------------------------------------
        */

        .navbar{
            background:white;
            padding:15px 25px;
            border-bottom:1px solid #ddd;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .navbar h3{
            font-size:20px;
        }

        /*
        |--------------------------------------------------------------------------
        | Content Area
        |--------------------------------------------------------------------------
        */

        .content{
            padding:25px;
        }

        /*
        |--------------------------------------------------------------------------
        | Dashboard Cards
        |--------------------------------------------------------------------------
        */

        .cards{
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(220px,1fr));
            gap:20px;
        }

        .card{
            background:white;
            padding:20px;
            border-radius:8px;
            box-shadow:0 2px 5px rgba(0,0,0,0.08);
        }

        .card h4{
            margin-bottom:10px;
            color:#555;
        }

        .card p{
            font-size:28px;
            font-weight:bold;
            color:#0d6efd;
        }

        /*
        |--------------------------------------------------------------------------
        | Tables
        |--------------------------------------------------------------------------
        */

        table{
            width:100%;
            border-collapse:collapse;
            background:white;
        }

        table th,
        table td{
            border:1px solid #ddd;
            padding:10px;
        }

        table th{
            background:#f1f5f9;
        }

        /*
        |--------------------------------------------------------------------------
        | Buttons
        |--------------------------------------------------------------------------
        */

        .btn{
            display:inline-block;
            padding:10px 15px;
            background:#0d6efd;
            color:white;
            text-decoration:none;
            border:none;
            border-radius:5px;
            cursor:pointer;
        }

        .btn:hover{
            opacity:0.9;
        }

        /*
        |--------------------------------------------------------------------------
        | Success Message
        |--------------------------------------------------------------------------
        */

        .alert-success{
            background:#d1e7dd;
            color:#0f5132;
            padding:12px;
            margin-bottom:20px;
            border-radius:5px;
        }

    </style>

</head>

<body>

<div class="wrapper">

    @include('layouts.partials.sidebar')

    <div class="main">

        @include('layouts.partials.navbar')

        <div class="content">

            @if(session('success'))

                <div class="alert-success">
                    {{ session('success') }}
                </div>

            @endif

            @yield('content')

        </div>

    </div>

</div>

</body>

</html>