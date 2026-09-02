<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

use App\Models\Domain;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;


class DomainController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $zoneId = config('services.cloudflare.zone_id');
        $apiToken = config('services.cloudflare.api_token');
        $urlBase = 'https://api.cloudflare.com/client/v4';

        $urlPeticion = "$urlBase/zones/$zoneId/dns_records";

        $dominios = [];

        // Peticion a la API de Cloudflare para obtener los registros DNS de la zona.
        $response = Http::withToken($apiToken)->get($urlPeticion);
        if ($response->successful()) {
            $rawRecords = $response->json('result', []);

            // Cloudflare responde con los registros dentro de la clave "result".
            // Conservamos A y CNAME y enviamos a React solo los campos que muestra la tabla.
            $dominios = collect($rawRecords)
                ->filter(fn ($record) => in_array($record['type'], ['A', 'CNAME']))
                ->map(fn ($record) => [
                    'id' => $record['id'],
                    'name' => $record['name'],
                    'type' => $record['type'],
                    'content' => $record['content'],
                    'proxied' => $record['proxied'],
                    'created_on' => $record['created_on'],
                ])
                ->values()
                ->all();
        }

        return Inertia::render('Dominios', [
            'dominios' => $dominios,
        ]);
       
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Domain $domain)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Domain $domain)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Domain $domain)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Domain $domain)
    {
        //
    }
}
