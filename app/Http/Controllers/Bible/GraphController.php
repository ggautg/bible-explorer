<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use App\Services\BibleGraphService;
use Inertia\Response;

/**
 * Controlador del grafo de referencias cruzadas.
 *
 * Construye y entrega los datos necesarios para visualizar las conexiones
 * entre un versículo central y sus referencias relacionadas.
 */
class GraphController extends Controller
{
    /**
     * Crea una nueva instancia del controlador.
     *
     * @param  BibleGraphService  $graphService  Servicio que construye el grafo de referencias.
     */
    public function __construct(private BibleGraphService $graphService) {}

    /**
     * Muestra el grafo centrado en un versículo específico.
     *
     * @param  string  $book  Código del libro.
     * @param  int  $chapter  Número de capítulo.
     * @param  int  $verse  Número de versículo.
     * @return Response Página Inertia con nodos y aristas del grafo.
     */
    public function show(string $book, int $chapter, int $verse): Response
    {
        return inertia('Bible/Graph', $this->graphService->buildGraph($book, $chapter, $verse));
    }
}
