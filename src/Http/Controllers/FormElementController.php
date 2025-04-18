<?php

namespace SleepingOwl\Admin\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SleepingOwl\Admin\Contracts\ModelConfigurationInterface;
use SleepingOwl\Admin\Form\Element\DependentSelect;
use SleepingOwl\Admin\Form\Element\MultiDependentSelect;
use SleepingOwl\Admin\Form\Element\MultiSelectAjax;
use SleepingOwl\Admin\Form\Element\SelectAjax;

class FormElementController extends Controller
{
    /**
     * @param  ModelConfigurationInterface  $model
     * @param  null  $id
     * @return JsonResponse|mixed
     */
    public function getModelLogic(ModelConfigurationInterface $model, $id = null)
    {
        if (! is_null($id)) {
            $item = $model->getRepository()->find($id);
            if (is_null($item) || ! $model->isEditable($item)) {
                return new JsonResponse([
                    'message' => trans('sleeping_owl::lang.message.access_denied'),
                ], 403);
            }

            return $model->fireEdit($id);
        }

        if (! $model->isCreatable()) {
            return new JsonResponse([
                'message' => trans('sleeping_owl::lang.message.access_denied'),
            ], 403);
        }

        return $model->fireCreate();
    }

    /**
     * @param  ModelConfigurationInterface  $model
     * @param  null  $id
     * @param  mixed  $payload
     * @return JsonResponse|mixed
     */
    public function getModelLogicPayload(ModelConfigurationInterface $model, $id = null, $payload = [])
    {
        if (! is_null($id)) {
            $item = $model->getRepository()->find($id);
            if (is_null($item) || ! $model->isEditable($item)) {
                return new JsonResponse([
                    'message' => trans('sleeping_owl::lang.message.access_denied'),
                ], 403);
            }

            return $model->fireEdit($id, $payload);
        }

        if (! $model->isCreatable()) {
            return new JsonResponse([
                'message' => trans('sleeping_owl::lang.message.access_denied'),
            ], 403);
        }

        return $model->fireCreate($payload);
    }

    /**
     * @param  Request  $request
     * @param  ModelConfigurationInterface  $model
     * @param  string  $field
     * @param  int|null  $id
     * @return JsonResponse
     */
    public function dependentSelect(Request $request, ModelConfigurationInterface $model, $field, $id = null)
    {
        $form = $this->getModelLogic($model, $id);

        if ($form instanceof JsonResponse) {
            return $form;
        }

        //for related element, hasMany, ManyToMany, BelongTo
        $isRelatedForm = preg_match('/\\w+\\[(\\d)\\]\\[(\\w+)\\]/', $field, $matches);

        $field_relate = $isRelatedForm ? $matches[2] : $field;

        // because field name in MultiDependentSelect ends with '[]'
        $fieldPrepared = str_replace('[]', '', $field_relate);

        /** @var DependentSelect|MultiDependentSelect $element */
        $element = $form->getElement($fieldPrepared);

        // because field name in MultiDependentSelect ends with '[]'
        $fieldPrepared = str_replace('[]', '', $field_relate);

        /** @var DependentSelect|MultiDependentSelect $element */
        $element = $form->getElement($fieldPrepared);

        if (is_null($element)) {
            return new JsonResponse([
                'message' => 'Element not found',
            ], 404);
        }

        $params = $request->input('depdrop_all_params', []);

        //related element, hasMany, ManyToMany, BelongTo
        if ($isRelatedForm) {
            $paramsRelated = [];
            collect($params)->each(function ($value, $key) use (&$paramsRelated) {
                $prepare_param = preg_match('/(.+)(_\d+)$/', $key, $matches);

                if (count($matches) > 0) {
                    $paramsRelated[$matches[1]] = $value;
                }
            });

            $params = count($paramsRelated) ? $paramsRelated : $params;
        }

        $element->setAjaxParameters(
            $params
        );

        $options = $element->getOptions();

        if ($element->isNullable()) {
            $options = [null => trans('sleeping_owl::lang.select.nothing')] + $options;
        }

        //for related element, hasMany, ManyToMany, BelongTo
        $addSelected = ! $isRelatedForm ? ['selected' => $element->getValueFromModel()] : [];

        return new JsonResponse([
            'output' => collect($options)->map(function ($value, $key) {
                return ['id' => $key, 'name' => $value];
            }),
        ] + $addSelected);
    }

    /**
     * @param  Request  $request
     * @param  ModelConfigurationInterface  $model
     * @param  string  $field
     * @param  int|null  $id
     * @return JsonResponse
     */
    public function multiselectSearch(Request $request, ModelConfigurationInterface $model, $field, $id = null)
    {
        return $this->selectSearch($request, $model, $field, $id);
    }

    /**
     * @param  Request  $request
     * @param  ModelConfigurationInterface  $model
     * @param  string  $field
     * @param  int|null  $id
     * @return JsonResponse
     */
    public function selectSearch(Request $request, ModelConfigurationInterface $model, $field, $id = null)
    {
        $form = $this->getModelLogic($model, $id);

        if ($form instanceof JsonResponse) {
            return $form;
        }

        // because field name in MultiSelectAjax ends with '[]'
        $fieldPrepared = str_replace('[]', '', $field);
        // process fields with relations: user[role]
        $fieldPreparedRel = strtr($fieldPrepared, ['[' => '.', ']' => '']);

        /** @var SelectAjax|MultiSelectAjax $element */
        $element = $form->getElement($fieldPreparedRel) ?: $form->getElement($fieldPrepared);

        if (is_null($element)) {
            return new JsonResponse([
                'message' => 'Element not found',
            ], 404);
        }

        $params = $request->input('depdrop_all_params', []);
        $temp = [];
        foreach ($params as $key => $val) {
            $key = strtr($key, ['[' => '.', ']' => '']);
            $key = strtr($key, ['__' => '.']);
            $temp[$key] = $val;
        }
        $params = $temp;

        $element->setAjaxParameters($params);

        if (is_callable($closure = $element->getModelForOptionsCallback())) {
            $model_classname = $closure($element);
        } else {
            $model_classname = $element->getModelForOptions();
        }
        if (is_object($model_classname)) {
            $model_classname = get_class($model_classname);
        }
        if ($model_classname && class_exists($model_classname)) {
            $model = new $model_classname;

            $search = $element->getSearch();
            if (is_callable($search)) {
                $search = $search($element);
            }
            $display = $element->getDisplay();
            $custom_name = $element->getCustomName();
            $exclude = $element->getExclude();

            if (is_object($model)) {
                $query = $model;
                $operator = $query->getConnection()->getQueryGrammar() instanceof PostgresGrammar ? config('sleeping_owl.search_operator') : 'LIKE';

                // search logic
                $model_table = $model->getTable();
                $q = $request->q;
                if (is_array($search)) {
                    $query = $query->where(function ($query) use ($operator, $model_table, $search, $q) {
                        foreach ($search as $key => $val) {
                            if (is_numeric($key)) {
                                $srch = $val;
                                $value = '%'.$q.'%';
                            } else {
                                $srch = $key;
                                switch ($val) {
                                    case 'equal':
                                        $value = $q;
                                        break;
                                    case 'begins_with':
                                        $value = $q.'%';
                                        break;
                                    case 'ends_with':
                                        $value = '%'.$q;
                                        break;
                                    case 'contains':
                                    default:
                                        $value = '%'.$q.'%';
                                        break;
                                }
                            }
                            $query = $query->orWhere($model_table.'.'.$srch, $operator, $value);
                        }
                    });
                } else {
                    $query = $query->where($model_table.'.'.$search, $operator, "%{$request->q}%");
                }

                // exclude result elements by id
                if (count($exclude)) {
                    $query = $query->whereNotIn($model_table.'.'.$model->getKeyName(), $exclude);
                }

                // call the pre load options query preparer if has be set
                if (is_callable($preparer = $element->getLoadOptionsQueryPreparer())) {
                    $query = $preparer($element, $query);
                }

                return new JsonResponse(
                    $query
                        ->get()
                        ->map(function (Model $item) use ($display, $element, $custom_name) {
                            if (is_string($display)) {
                                $value = $item->{$display};
                            } elseif (is_callable($display)) {
                                $value = $display($item, $element);
                            } else {
                                $value = null;
                            }
                            if (is_string($custom_name)) {
                                $custom_name_value = $item->{$custom_name};
                            } elseif (is_callable($custom_name)) {
                                $custom_name_value = $custom_name($item, $element);
                            } else {
                                $custom_name_value = null;
                            }

                            return [
                                'tag_name' => $value,
                                'id' => $item->{$item->getKeyName()},
                                'custom_name' => $custom_name_value,
                            ];
                        })
                );
            }
        }

        return new JsonResponse([]);
    }
}
