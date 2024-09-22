<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Customer\IndexRequest;
use App\Http\Requests\Api\Customer\StoreRequest;
use App\Http\Requests\Api\Customer\UpdateRequest;
use App\Http\Requests\Api\Customer\DeleteRequest;
use App\Models\Customer;
use App\Traits\PartyTraits;
use http\Env\Request;

class CustomersController extends ApiBaseController
{
	use PartyTraits;

	protected $model = Customer::class;

	protected $indexRequest = IndexRequest::class;
	protected $storeRequest = StoreRequest::class;
	protected $updateRequest = UpdateRequest::class;
	protected $deleteRequest = DeleteRequest::class;

	public function __construct()
	{
		parent::__construct();

		$this->userType = "customers";
	}


    public function AllocationUserSearch(Request $request)
    {
        $warehouse = warehouse();
        $query=null;

//        if ($request->has('search_due_type') && $request->search_due_type != "") {
//            if ($request->search_due_type == "receive") {
//                $query = $query->where('user_details.due_amount', '>=', 0);
//            } else {
//                $query = $query->where('user_details.due_amount', '<=', 0);
//            }
//        }
//
//        if (
//            ($this->userType == 'customers' && $warehouse->customers_visibility == 'warehouse') ||
//            ($this->userType == 'suppliers' && $warehouse->suppliers_visibility == 'warehouse')
//        ) {
//            $query = $query->where(function ($query) use ($warehouse) {
//                $query->where('users.warehouse_id', '=', $warehouse->id)
//                    ->orWhere('users.is_walkin_customer', '=', 1);
//            });
//        }
//
//        $query = $query->join('user_details', 'user_details.user_id', '=', 'users.id')
//            ->where('user_details.warehouse_id', $warehouse->id);
//        return $query;
        json_decode($warehouse);
    }
}
