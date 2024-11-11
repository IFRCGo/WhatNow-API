<?php

namespace App\Classes\Repositories;

interface RepositoryInterface {

	public function all($columns = ['*']);

	public function newInstance(array $attributes = []);

	public function create(array $attributes);

	public function find($id, $columns = ['*']);

	public function updateWithIdAndInput($id, array $input);

	public function destroy($id);
}
