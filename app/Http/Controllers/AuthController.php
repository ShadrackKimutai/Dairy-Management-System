<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Hash;
use Session;
use App\Models\User;
use App\Models\Cow;
use App\Models\Production;
use App\Models\Roles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;



class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }  
      
    public function Login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
            
        ]);
   
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->intended('dashboard')
                        ->withSuccess('Signed in');
        }
  
        return redirect("login")->withSuccess('Login details are not valid');
    }

    public function registration()
    {
        return view('auth.registration');
    }
      
    public function userRegistration(Request $request)
    {  
        $request->validate([
            'name' => 'required',
            'national_id'=>'required|unique:User',
            'dob'=>'required',
            'email' => 'required|email|unique:User',
            'phone'=>'required|min:10|max:11',
            'password' => 'required|min:8'
        ]);
           
        $data = $request->all();
        $check = $this->create($data);
         
        return redirect("dashboard")->withSuccess('You have signed-in');
    }

    public function create(array $data)
    {
      return User::create([
        'name' => $data['name'],
        'national_id'=> $data['national_id'],
        'dob'=>$data['dob'],
        'phone'=>$data['phone'],
        'email' => $data['email'],
        'password' => Hash::make($data['password'])
      ]);
    }    
    
    public function dashboard()
    {
        $timeofday=(date('H'));
       $production_time;
       switch($timeofday){
        case $timeofday < 10:
            $production_time="Morning";
            break;
        case $timeofday>=10 && $timeofday<15:
            $production_time="Mid Day";
            break;
        case $timeofday>=15:
            $production_time="Evening";
            break;
       }
        
        if(Auth::check()){
            $start = Carbon::today()->subDays(7);

            $end = Carbon::yesterday();
            $production=DB::select('Select sum(amount) as sum from Production where production_date="'.date('Y-m-d').'" AND production_period="'.$production_time.'"');
            $daysproduction = DB::select('Select sum(amount) as sum from Production where production_date="'.date('Y-m-d').'"');
            $herdsize=Cow::all()->count();
            $herd =Cow::all();
            $users=User::all()->count();
            $productionTime=['Morning','Afternoon','Evening'];
            $productionlist=collect(DB::select('SELECT tag,sum(amount) AS amount FROM Production GROUP BY tag,production_date ORDER By tag'));
            $productionlist=$productionlist->groupby('tag');
            $productionlist->all();
            $dates = $this->generateDates($start, $end); // you fill zero valued dates
             $productionlist=[];
             $count=0;
            foreach ($dates as $date){
                foreach ($productionTime as $timeofday){
                  //foreach( as $cow){
                    
                    $productionlist[]=[$count,$date,$timeofday];
                    $count+=1;

                }
            }
           // $productiondates->$dates->keys();
           
           // $productionvalues=$productionlist->values();
            dd($herd);
            return view('dashboard',compact('production','daysproduction','production_time','herdsize','users','labels','productionvalues'));
        }
  
        return redirect("login")->withSuccess('You are not allowed to access');
    }
    private function generateDates(Carbon $startDate, Carbon $endDate, $format = 'Y-m-d'){
        $dates = [];
        $startDate = $startDate->copy();
        $count=0;
            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $dates[$count]=$date->format($format);
                $count+=1;
                
            }
       return $dates;
    }
    public function signOut() {
        Session::flush();
        Auth::logout();
  
        return Redirect('/');
    }
}
