<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Request;
use App\Core\Response;
use App\Core\ViewRenderer;
use App\Domain\SearchCriteria;
use App\Infrastructure\ApiException;
use App\Service\CharacterService;
use Throwable;

final readonly class CharacterController
{
    private const STATUSES = [
        '' => 'Any status',
        'alive' => 'Alive',
        'dead' => 'Dead',
        'unknown' => 'Unknown',
    ];

    private const GENDERS = [
        '' => 'Any gender',
        'female' => 'Female',
        'male' => 'Male',
        'genderless' => 'Genderless',
        'unknown' => 'Unknown',
    ];

    public function __construct(
        private CharacterService $characters,
        private ViewRenderer $views,
    ) {
    }

    public function index(Request $request): Response
    {
        $criteria = $this->criteriaFrom($request);
        $error = null;

        try {
            $page = $this->characters->search($criteria);
        } catch (Throwable) {
            $page = null;
            $error = 'The character list could not be loaded right now. Please try again shortly.';
        }

        return new Response($this->views->render('characters/index', [
            'title' => 'Rick and Morty Encyclopedia',
            'criteria' => $criteria,
            'error' => $error,
            'page' => $page,
            'statuses' => self::STATUSES,
            'genders' => self::GENDERS,
        ]));
    }

    /**
     * @param array<string, string> $parameters
     */
    public function show(Request $request, array $parameters): Response
    {
        unset($request);

        $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if (! is_int($id)) {
            return $this->notFound();
        }

        try {
            $details = $this->characters->details($id);
        } catch (ApiException $exception) {
            if ($exception->statusCode() === 404) {
                return $this->notFound();
            }

            return $this->serviceUnavailable();
        } catch (Throwable) {
            return $this->serviceUnavailable();
        }

        return new Response($this->views->render('characters/show', [
            'title' => $details->character->name,
            'details' => $details,
        ]));
    }

    private function criteriaFrom(Request $request): SearchCriteria
    {
        $status = strtolower($request->string('status', 20));
        $gender = strtolower($request->string('gender', 20));

        return new SearchCriteria(
            page: $request->integer('page', 1, 1, 500),
            name: $request->string('q', 80),
            status: array_key_exists($status, self::STATUSES) ? $status : '',
            species: $request->string('species', 60),
            gender: array_key_exists($gender, self::GENDERS) ? $gender : '',
        );
    }

    private function notFound(): Response
    {
        return new Response($this->views->render('errors/404', [
            'title' => 'Character not found',
        ]), 404);
    }

    private function serviceUnavailable(): Response
    {
        return new Response($this->views->render('errors/503', [
            'title' => 'Service unavailable',
        ]), 503);
    }
}
