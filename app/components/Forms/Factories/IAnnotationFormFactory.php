<?php

namespace App\Components\Forms\Factories;

use App\Components\Forms\AnnotationForm;

interface IAnnotationFormFactory
{
	/**
	 * @return \App\Forms\AnnotationForm
	 */
	public function create(): AnnotationForm;
}
