<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeLoans extends Model
{
    protected $fillable = [
        'branch_id',
        'last_name',	
        'first_name',	
        'middle_name',	
        'position',	
        'loan_type',	
        'description',
        'ref_no',
        'date_granted',	
        'principal_loan',	
        'interest',	
        'total_loan',	
        'terms',	
        'period_from',	
        'period_to',	
        'monthly_amortization',	
        'total_paid',	
        'balance',	
        'remaining_term',	
        'or_number',							
    ];

    public function branch()
    {
        return $this->hasOne('App\Branch', 'id', 'branch_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
