<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Ditolak | ADI CELL POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e0e7f0 0%, #f0f4fa 50%, #e8edf5 100%);
            min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .error-card {
            background: #fff; border-radius: 16px; padding: 3rem 2rem; text-align: center;
            max-width: 420px; box-shadow: 0 4px 24px rgba(0,0,0,0.06), 0 1px 4px rgba(0,0,0,0.04);
        }
        .error-code { font-size: 4rem; font-weight: 800; color: #ef4444; line-height: 1; }
        .error-text { font-size: 0.95rem; color: #64748b; margin: 0.75rem 0 1.5rem; }
        .btn-back {
            background: #3b82f6; border: none; border-radius: 8px; color: #fff; padding: 0.5rem 1.5rem;
            text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-block; transition: all 0.15s;
        }
        .btn-back:hover { background: #2563eb; color: #fff; }
        .btn-back i { margin-right: 0.4rem; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">403</div>
        <div class="error-text">
            @if($exception?->getMessage())
                {{ $exception->getMessage() }}
            @else
                Anda tidak memiliki izin untuk mengakses halaman ini.
            @endif
        </div>
        <a href="{{ Auth::check() && (Auth::user()->isAdmin() || Auth::user()->hasPermission(config('permissions.DASHBOARD'))) ? route('dashboard') : route('stock.sales') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</body>
</html>
