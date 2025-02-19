<?php

namespace App\Classes\Repositories;

use App\Models\Organisation;
use App\Models\Contributor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrganisationRepository implements OrganisationRepositoryInterface
{
    /**
     * @var Organisation
     */
    protected $orgModel;

    /**
     * @param Organisation $orgModel
     */
    public function __construct(Organisation $orgModel)
    {
        $this->orgModel = $orgModel;
    }

    /**
     * @param array $attributes
     * @return static
     */
    public function newInstance(array $attributes = [])
    {
        return $this->orgModel->newInstance($attributes);
    }

    /**
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all($columns = ['*'])
    {
        return $this->orgModel->all($columns);
    }

    /**
     * @param array $attributes
     * @return static
     */
    public function create(array $attributes)
    {
        return $this->orgModel->create($attributes);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->orgModel->findOrFail($id, $columns);
    }

    /**
     * @param $id
     * @param array $input
     * @return mixed
     */
    public function updateWithIdAndInput($id, array $input)
    {
        return $this->orgModel->where('id', $id)->update($input);
    }

    /**
     * @param Organisation $org
     * @param array $input
     * @return mixed
     */
    public function updateDetailsWithInput(Organisation $org, array $input)
    {
        Log::error(print_r($input, true));

        if (array_key_exists('url', $input)) {
            $org->update([
                'attribution_url' => $input['url'],
            ]);
        }

        if (array_key_exists('translations', $input)) {
            if (count($input['translations'])) {
                DB::transaction(function () use ($org, $input) {
                    $org->details()->delete();
                    
                    foreach ($input['translations'] as $lang => $data) {
                        $currentDetail = $org->details()->updateOrCreate([
                            'language_code' => strtolower($lang),
                        ], [
                            'language_code' => strtolower($lang),
                            'org_name' => $data['name'] ?? '',
                            'attribution_message' => $data['attributionMessage'] ?? '',
                            'published' => $data['published'] ?? false,
                        ]);

                        if (array_key_exists('contributors', $data)) {
                            $currentDetail->contributors()->delete();

                            foreach ($data['contributors'] as $contributor) {
                                $contributor = new Contributor([
                                    'name' => $contributor['name'],
                                    'logo' => $contributor['logo'],
                                ]);

                                $isValid = $contributor->validate($contributor->toArray());

                                if (!$isValid) {
                                    Log::error('Contributor validation failed', ['errors' => $contributor->errors()->toArray()]);
                                }

                                $currentDetail->contributors()->save($contributor);
                            }
                        }
                    }
                });
            }
        }
    }

    /**
     * @param $id
     * @return int
     */
    public function destroy($id)
    {
        return $this->orgModel->destroy($id);
    }

    /**
     * @param $code
     * @return mixed
     */
    public function findByCountryCode($code)
    {
        return $this->orgModel->where('country_code', $code)->firstOrFail();
    }
}
