<?php
namespace Tests\Feature;

use App\Http\Controllers\Api\Admin\MerchantEmployeeController;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\Merchant;
use App\Services\Employee\EmployeeService;
use App\Services\Merchant\MerchantEmployeeService;
use App\Services\Merchant\MerchantService;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class AdminMerchantEmployeeTest extends TestCase
{
    private function controller($employees): MerchantEmployeeController
    {
        $merchant = new Merchant();
        $merchant->id = 42;
        $merchants = Mockery::mock(MerchantService::class);
        $merchants->shouldReceive('show')->once()->with('merchant-1')->andReturn($merchant);
        return new MerchantEmployeeController($merchants, Mockery::mock(MerchantEmployeeService::class), $employees);
    }

    public function test_create_uses_selected_merchant(): void
    {
        $request = Mockery::mock(StoreEmployeeRequest::class);
        $request->shouldReceive('validated')->once()->andReturn(['first_name' => 'Jane']);
        $employee = new Employee(['uuid' => 'employee-1', 'first_name' => 'Jane']);
        $employee->setRelation('user', null);
        $service = Mockery::mock(EmployeeService::class);
        $service->shouldReceive('store')->once()->with(42, ['first_name' => 'Jane'])->andReturn($employee);
        $this->assertSame(201, $this->controller($service)->store($request, 'merchant-1')->getStatusCode());
    }

    public function test_update_checks_merchant_before_saving(): void
    {
        $employee = new Employee(['uuid' => 'employee-1']);
        $employee->setRelation('user', null);
        $request = Mockery::mock(UpdateEmployeeRequest::class);
        $request->shouldReceive('validated')->once()->andReturn(['first_name' => 'Jane']);
        $service = Mockery::mock(EmployeeService::class);
        $service->shouldReceive('show')->once()->with('employee-1', 42)->andReturn($employee);
        $service->shouldReceive('update')->once()->with($employee, ['first_name' => 'Jane'])->andReturn($employee);
        $this->assertSame(200, $this->controller($service)->update($request, 'merchant-1', 'employee-1')->getStatusCode());
    }

    public function test_employee_outside_merchant_cannot_be_updated(): void
    {
        $service = Mockery::mock(EmployeeService::class);
        $service->shouldReceive('show')->once()->with('other-employee', 42)
            ->andThrow(ValidationException::withMessages(['employee' => 'Employee not found.']));
        $service->shouldNotReceive('update');
        $request = Mockery::mock(UpdateEmployeeRequest::class);
        $request->shouldNotReceive('validated');
        $this->expectException(ValidationException::class);
        $this->controller($service)->update($request, 'merchant-1', 'other-employee');
    }
}
