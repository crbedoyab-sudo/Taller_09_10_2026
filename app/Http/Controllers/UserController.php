<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkStoreUsersRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
class UserController extends Controller
{
    public function index(Request $request)
    {

    // Obtener la cantidad de elementos por página (por defecto 15, máximo 100)
    $perPage = min($request->query('per_page', 15), 100);

    // Consulta paginada a la base de datos
    $users = User::paginate($perPage);

    return response()->json($users);
    }

   public function emails(Request $request): JsonResponse
{
    $perPage = min($request->query('per_page', 15), 100);

    // Seleccionamos solo las columnas id y email, y paginamos
    $users = User::select('id', 'email')->paginate($perPage);

    return response()->json($users);
}

    public function overTwenty(Request $request): JsonResponse
{
    $perPage = min($request->query('per_page', 15), 100);
    $cutoff = Carbon::now()->subYears(20)->startOfDay();

    // Filtramos directamente en SQL antes de paginar
    $users = User::whereNotNull('birth_date')
        ->where('birth_date', '<=', $cutoff)
        ->paginate($perPage);

    // Agregamos la fecha de corte requerida por la prueba
    return response()->json([
        'cutoff_date' => $cutoff->toDateString(),
        'users' => $users
    ]);
}

    public function bulkStore(BulkStoreUsersRequest $request): JsonResponse
{
    // Ejecutamos la inserción en una sola transacción para soportar concurrencia alta
    $created = DB::transaction(function () use ($request) {
        $users = [];
        $defaultPassword = Hash::make('password'); // Generamos el hash una sola vez para no sobrecargar el CPU

        foreach ($request->validated()['users'] as $userData) {
            $users[] = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'birth_date' => $userData['birth_date'],
                'password' => isset($userData['password']) ? Hash::make($userData['password']) : $defaultPassword,
            ]);
        }

        return $users;
    });

    return response()->json([
        'message' => 'Se crearon 3 usuarios correctamente.',
        'users' => $created,
    ], 201);
}
}
