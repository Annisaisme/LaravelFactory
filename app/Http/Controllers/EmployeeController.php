<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Alert;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pageTitle = 'Employee List';
        //  // RAW SQL QUERY
        //  $employees = DB::select(' select *, employees.id as employee_id, positions.name as position_name from employees
        //   left join positions on employees.position_id = positions.id
        //   ');

          //QUERY BUILDER
        //   $employees = DB::table('employees')
        //   ->select('*','employees.id as employee_id','positions.name as position_name')
        //   ->leftJoin('positions','employees.position_id','=','positions.id')
        //   ->get();

          // ELOQUENT
          $employees = Employee::all();

        return view('employee.index', [
            'pageTitle' => $pageTitle,
            'employees' => $employees
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pageTitle = 'Create Employee';
        // RAW SQL Query
        // $positions = DB::select('select * from positions');

        //QUERY BUILDER
        // $positions = DB::table('positions')->get();

        // ELOQUENT
        $positions = Position::all();

        return view('employee.create', compact('pageTitle', 'positions'));

    }

    public function store(Request $request)
    {

        $messages = [
            'required' => ':Attribute harus diisi.',
            'email' => 'Isi :attribute dengan format yang benar',
            'numeric' => 'Isi :attribute dengan angka'
        ];

        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'age' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get File
        $file = $request->file('cv');

        if ($file != null) {
            $originalFilename = $file->getClientOriginalName();
            $encryptedFilename = $file->hashName();

            // Store File
            $file->store('public/files');
        }

        // ELOQUENT
        $employee = New Employee;
        $employee->firstname = $request->firstName;
        $employee->lastname = $request->lastName;
        $employee->email = $request->email;
        $employee->age = $request->age;
        $employee->position_id = $request->position;

        if ($file != null) {
            $employee->original_filename = $originalFilename;
            $employee->encrypted_filename = $encryptedFilename;
        }

        $employee->save();

    // INSERT QUERY
    // DB::table('employees')->insert([
    //     'firstname' => $request->firstName,
    //     'lastname' => $request->lastName,
    //     'email' => $request->email,
    //     'age' => $request->age,
    //     'position_id' => $request->position,
    // ]);

    return redirect()->route('employee.index');
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pageTitle = 'Employee Detail';

        // // RAW SQL QUERY
        // $employee = collect(DB::select('
        //     select *, employees.id as employee_id, positions.name as position_name
        //     from employees
        //     left join positions on employees.position_id = positions.id
        //     where employees.id = ?
        // ', [$id]))->first();

        // QUERY BUILDER
        // $employee = DB::table('employees')
        // ->select('*','employees.id as employee_id','positions.name as position_name')
        // ->leftJoin('positions','employees.position_id','=','positions.id')
        // ->where('employees.id','=',$id)
        // ->first();

        // ELOQUENT
        $employee = Employee::find($id);

        return view('employee.show', compact('pageTitle', 'employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pageTitle = 'Edit Employee';
        // $positions = DB::table('positions')->get();
        // $employee = DB::table('employees')
        //     ->select('*','employees.id as employee_id','positions.name as position_name')
        //     ->leftJoin('positions','employees.position_id','positions.id')
        //     ->where('employees.id',$id)
        //     ->first();

        // ELOQUENT
        $positions = Position::all();
        $employee = Employee::find($id);

            return view('employee.edit',compact('pageTitle','positions','employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $messages = [
            'required' => ':Attribute harus diisi.',
            'email' => 'Isi :attribute dengan format yang benar',
            'numeric' => 'Isi :attribute dengan angka'
        ];

        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'age' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $file = $request->file('cv');
        if ($file != null){
        $employee = Employee::find($id);
        $encryptedFilename = 'public/files/'.$employee->encrypted_filename;
        Storage::delete($encryptedFilename);
        }
        if ($file != null) {
        $originalFilename = $file->getClientOriginalName();
        $encryptedFilename = $file->hashName();

        // Store File
        $file->Store('public/files');
        }

        // ELOQUENT
        $employee = Employee::find($id);
        $employee->firstname = $request->firstName;
        $employee->lastname = $request->lastName;
        $employee->email = $request->email;
        $employee->age = $request->age;
        $employee->position_id = $request->position;

        if ($file != null) {
            $employee->original_filename = $originalFilename;
            $employee->encrypted_filename = $encryptedFilename;
            }

        $employee->save();

        return redirect()->route('employee.index');

        // return view('employee.index',compact('pageTitle','employee'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // QUERY BUILDER
        // DB::table('employees')
        // ->where('id', $id)
        // ->delete();

        Employee::find($id);
     $employee = Employee::findOrFail($id);

     // Hapus file CV jika ada
     if ($employee->encrypted_filename && Storage::disk('public')->exists('files/' . $employee->encrypted_filename)) {
            Storage::disk('public')->delete('files/' . $employee->encrypted_filename);
            $employee->original_filename = null;
            $employee->encrypted_filename = null;
    }

     // Hapus data karyawan
     $employee->delete();

    //  Alert::success('Deleted Successfully', 'Employee Data Deleted Successfully.');
     return redirect()->route('employee.index');
    }

    public function downloadFile($employeeId)
    {
        $employee = Employee::find($employeeId);
        $encryptedFilename = 'public/files/'.$employee->encrypted_filename;
        $downloadFilename = Str::lower($employee->firstname.'_'.$employee->lastname.'_cv.pdf');

        if(Storage::exists($encryptedFilename)) {
            return Storage::download($encryptedFilename, $downloadFilename);
        }
    }
}
