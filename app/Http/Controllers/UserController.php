<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserIndexResource;
use App\Http\Requests\UserRequest\StoreUserAdminRequest;
use App\Http\Requests\UserRequest\UpdateUserAdminRequest;
use App\Http\Requests\UserRequest\UpdateUserRequest;
use App\Http\Resources\User\UserListResource;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Services\ImportServices\ImportService;
use App\Services\EmailServices\EmailService;

class UserController extends Controller
{
    protected ImportService $importService;
    protected EmailService $emailService;

    public function __construct(ImportService $importService, EmailService $emailService)
    {
        $this->importService = $importService;
        $this->emailService = $emailService;
    }
    /* index no se ocupa porque no vamos a mostrar usuarios en un selector */

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserAdminRequest $request)/* crea usuario con correo,contraseña y confirmación de contraseña */
    {
        try {
            $user = User::create($request->validated());

            /* manda el correo de notificación del usuario */
            $this->emailService->sendEmail('emails.welcome','Tu cuenta ha sido creada exitosamente.','Tu contraseña es: '.$request->validated()['password'],$user->email);

            return response()->json([
                'message' => 'Usuario  creado con exito',
            ], 201);
        } catch (\Throwable $e) {
           return response()->json([
                'message' => 'El usuario no se pudo crear',
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $user = User::withTrashed()->with(['role', 'occupation', 'dependency', 'institution'])->find($decryptedId);

            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            return new UserResource($user);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    /* actualiza correo y contraseña y manda correo al usuario de que su contraseña a sido reestablecida */
    public function update(UpdateUserAdminRequest $request, string $id)
    {
       try {
            $decryptedId = Crypt::decryptString($id);
            $user = User::find($decryptedId);

            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            if (empty($request->validated())) {
                return response()->json([
                    'message' => 'No se enviaron datos para actualizar.',
                ], 400);
            }

            $user->update($request->validated());

            $validated = $request->validated();

            if (array_key_exists('password', $validated)) {
                $this->emailService->sendEmail('emails.welcome','Tu cuenta ha sido actualizada exitosamente.','Tu contraseña es: ' . $validated['password'],$user->email);
            }

            if (array_key_exists('email', $validated)) {
                $this->emailService->sendEmail('emails.welcome','Tu cuenta ha sido actualizada exitosamente.','Tu correo ha sido actualizado.',$user->email);
            }
            
            return response()->json([
                'message' => 'Usuario actualizado con éxito.',
            ], 200);
            
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    /* actualiza toda la informacion del usuario */
    public function updateUser(UpdateUserRequest $request,string $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $user = User::find($decryptedId);

            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            if (empty($request->validated())) {
                return response()->json([
                    'message' => 'No se enviaron datos para actualizar.',
                ], 400);
            }

            $user->update($request->validated());

            $validated = $request->validated();

            if (array_key_exists('password', $validated)) {
                $this->emailService->sendEmail('emails.welcome','Tu cuenta ha sido actualizada exitosamente.','Tu contraseña es: ' . $validated['password'],$user->email);
            }else{/* si no fue la contraseña y fue otra cosa solo se manda el mensaje de que su cuenta fue actualizada */
                $this->emailService->sendEmail('emails.welcome','Tu cuenta ha sido actualizada exitosamente.','Tu información de usuario ha sido actualizada correctamente.',$user->email);
            }

            if (array_key_exists('email', $validated)) {
                $this->emailService->sendEmail('emails.welcome','Tu cuenta ha sido actualizada exitosamente.','Tu correo ha sido actualizado.',$user->email);
            }else{/* si no fue la contraseña y fue otra cosa solo se manda el mensaje de que su cuenta fue actualizada */
                $this->emailService->sendEmail('emails.welcome','Tu cuenta ha sido actualizada exitosamente.','Tu información de usuario ha sido actualizada correctamente.',$user->email);
            }

            return response()->json([
                'message' => 'Usuario actualizado con éxito.',
            ], 200);
            
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)//soft delete revisa en todos los registros activos
    {
         try {
            $decryptedId = Crypt::decryptString($id);
            $user = User::find($decryptedId);

            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            /* desactivamor el recurso */
            $user->delete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Usuario desactivado con exito',
            ], 200);


        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    public function restore(string $id)//reactivar un recurso eliminado y solo busca en los eliminados
    {
         try {
            $decryptedId = Crypt::decryptString($id);
            $user = User::onlyTrashed()->find($decryptedId);
 
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            /* reactivamos el recurso */
            $user->restore();
            //mensaje de exito 
            return response()->json([
                'message' => 'Usuario activado con exito',
            ], 200);


        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    public function forceDelete(string $id)//busca solo en los eliminados para eliminar definitivamente
    {
         try {
            $decryptedId = Crypt::decryptString($id);
            $user = User::onlyTrashed()->find($decryptedId);

            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            /* eliminamos definitivamente el recurso */
            $user->forceDelete();
            //mensaje de exito 
            return response()->json([
                'message' => 'Usuario eliminado definitivamente con exito',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }

    //metodo content para el soft delete
    public function content($content)/* falta filtrar por roles ocupacion y algo mas que se ocurra */
    {
        try {
            switch ($content) {
                case 'active':
                    return UserListResource::collection(User::paginate(10));
                    break;
                case 'inactive':
                    return UserListResource::collection(User::onlyTrashed()->paginate(10));
                    break;
                case 'all':
                    return UserListResource::collection(User::withTrashed()->paginate(10));
                    break;
                default:
                    return response()->json([
                        'message' => 'Contenido no válido. Use "active", "inactive" o "all".',
                    ], 400);
                }

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
            ], 400);
        }
    }
}