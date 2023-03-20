<?php

class DefaultPage extends \Kirby\Cms\Page
{
	public function whoAmI(): array
	{
		return [
			'status' => 200,
			'message' => 'You are ' . kirby()->users()->current()?->id(),
		];
	}

	public function repeatAfterMe($data): array
	{
		return [
			'status' => 200,
			'message' => 'Repeat after me: ' . $data,
		];
	}
}
