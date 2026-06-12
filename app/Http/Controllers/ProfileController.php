<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function completeProfile(Request $request)/* aqui se completa la info del perfil del usuario */
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)/* ver el perfil aqui se manda el id o el token */
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)/* actualiza el perfil del usuario pero no todo */
    {
        //
    }
}
