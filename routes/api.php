<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

$VERSION = 'v1';

//User
require "$VERSION/user/api.php";

//Session
require "$VERSION/session/api.php";

//Task
require "$VERSION/task/api.php";

//Subject
require "$VERSION/subject/api.php";

/*//Accounting accounts
require "$VERSION/accounting_account/api.php";

//Areas
require "$VERSION/area/api.php";

//Cranes
require "$VERSION/crane/api.php";

//Parking lots
require "$VERSION/parking_lot/api.php";

//Accounting definition
require "$VERSION/accounting_definition/api.php";

//Socioeconomic study
require "$VERSION/socioeconomic_study/api.php";

//Model authorizer
require "$VERSION/model_authorizer/api.php";

    //Authorization
    require "$VERSION/authorization/api.php";

    //Authorization rule
    require "$VERSION/authorization_rule/api.php";

//Role
require "$VERSION/role/api.php";

//Permission
require "$VERSION/permission/api.php";

//Concept
require "$VERSION/concept/api.php";

//Vehicle family
require "$VERSION/vehicle_family/api.php";

//Accouting passsing
require "$VERSION/accounting_passing/api.php";

//Request
require "$VERSION/request/api.php";

//Accounting operations
require "$VERSION/accounting_operation/api.php";

//Cash cut
require "$VERSION/cash_cut/api.php";

//Payment
require "$VERSION/payment/api.php";

//Phone
require "$VERSION/phone/api.php";

//Ticket
require "$VERSION/ticket/api.php";

//Vehicle data
require "$VERSION/vehicle_data/api.php";

//Inventory
require "$VERSION/inventory/api.php";

//Spot
require "$VERSION/spot/api.php";

//Storage container
require "$VERSION/storage_container/api.php";

//Account daily lodge
require "$VERSION/account_daily_lodge/api.php";

//judge_Analysis
require "$VERSION/judge_analysis/api.php";

//Stock
require "$VERSION/stock/api.php";

//Thing
require "$VERSION/thing/api.php";

//Zone
require "$VERSION/zone/api.php";

//Brand
require "$VERSION/brand/api.php";

//Address
require "$VERSION/address/api.php";

//Applicant
require "$VERSION/applicant/api.php";

//Auction
require "$VERSION/auction/api.php";

//Authority
require "$VERSION/authority/api.php";

//Bailment
require "$VERSION/bailment/api.php";

//Credit note
require "$VERSION/credit_note/api.php";

//Daily fee
require "$VERSION/daily_fee/api.php";

//Detention reason
require "$VERSION/detention_reason/api.php";

//Discount budget
require "$VERSION/discount_budget/api.php";

//Email
require "$VERSION/email/api.php";

//Base account
require "$VERSION/base_account/api.php";

//Branch
require "$VERSION/branch/api.php";

//Customer
require "$VERSION/customer/api.php";

//Bank
require "$VERSION/bank/api.php";

//Contact
require "$VERSION/contact/api.php";

//Employee
require "$VERSION/employee/api.php";

//Folio
require "$VERSION/folio/api.php";

//Holiday
require "$VERSION/holiday/api.php";

//Image
require "$VERSION/image/api.php";

//Country (+ states + cities)
require "$VERSION/country/api.php";

//Vehicle subfamily rates
require "$VERSION/vehicle_subfamily_rates/api.php";

//Cost details
require "$VERSION/cost_details/api.php";

//Audit log
require "$VERSION/audit_log/api.php";

//Repuve
require "$VERSION/repuve/api.php";

//Busca tu auto
require "$VERSION/busca_tu_auto/api.php";

//Hacienda
require "$VERSION/hacienda/api.php";

//Cashier
require "$VERSION/cashier/api.php";

//Files
require "$VERSION/file/api.php";

//File Concept
require "$VERSION/file_concept/api.php";

//Send receipt
require "$VERSION/send_receipt/api.php";*/