<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use Validator;
use App\Models\User;
use App\Models\PersonalAccessToken;
use Illuminate\Foundation\Auth\ThrottlesLogins;

class AuthController extends Controller
{
	/** Add This line on top */
	use ThrottlesLogins;
	/** This way, you can control the throttling */
	protected $maxLoginAttempts=3;
	protected $lockoutTime=300;
	protected $username = 'username';

	protected $expiration = 1440; // durée de validité du token en minute

	/**
     * Page index.
     *
     * @return \Illuminate\Http\Response
     */
	public function index()
	{
		return response()->json(array("error"=>false, "message"=>"Envoyez dans la page index"))->setStatusCode(200);
	}

	/**
     * Inscription d'un utilisateur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	public function register(Request $request)
	{
		$validator = Validator::make($request->all(), [
                'firstname' => 'required',
                'date_naissance' => 'required',
                'sexe' => 'required',
                'email' => 'required|email',
                'password' => 'required|confirmed|min:6',
                'password_confirmation' => 'required',
            ],[
                'firstname.required'=>1,
                'date_naissance.required'=>1,
                'sexe.required'=>1,
                'email.required'=>1,
                'password.required'=>1,
                'password_confirmation.required'=>1,

                'password.min' =>2,
                'password.confirmed' => 2,
                'email.email' =>2,
            ]);
        if($validator->passes()) {
            $user = new User();

            $login = self::checkLogin($request->get('email'));
            if($login === true){
                $error["error"] = true;
                $error["message"] = 'Votre email est déjà utiliser';
                return response()->json($error)->setStatusCode(401); 
            }else{
                $request->password = Hash::make($request->get('password'));
                $user->fill($request->all());
                $user->password = $request->password;
                $user->save();
                $request->id_user = $user->id;
                $token = $user->createToken('auth_token')->plainTextToken;

                $response = array();
                $response["error"] = false;
                $response["message"] = "L'utilisateur a bien été créé avec succés";
                $response["tokens"] = array(
                					"token"=>$token,
                					"refresh-token"=>"Sayna",
                					"createdAt"=>$user->created_at
                					);
                return response()->json($response)->setStatusCode(201);
                //print_r($token);die;
            }
        }else{
            $error_array = array("error"=>1);

            foreach ($validator->messages()->getMessages() as $field_name => $messages)
            {
                if($messages[0] == 1){
                    return response()->json(array("error"=>true, "message"=>"L'une ou plisuers des données obligatoire sont manquantes"))->setStatusCode(401);
                }
                elseif($messages[0] == 2){
                    return response()->json(array("error"=>true, "message"=>"L'une des données obligatoire ne sont pas conformes"))->setStatusCode(401);
                }
                else{
                    return response()->json(array("error"=>true, "message"=>$messages[0]))->setStatusCode(401);
                }
            }
            //return response()->json($error_array);
        }
	}

	/**
     * Verification de l'address mail.
     *
     * @param  String $email
     * @return bool true||false
     */
	function checkLogin($email)
    {
        $data2 = User::where('email',$email)->get()->toArray();
        if(count($data2) > 0){
            return true;
        }else{
            return false;
        }
    }


    /**
     * Authentification d'un utilisateur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	public function login(Request $request)
	{
		if ($this->hasTooManyLoginAttempts($request)) {
	        $this->fireLockoutEvent($request);
	        return response()->json(array("error"=>true, "message"=>"Trop de tentative sur l'email $request->email - Veuillez patientez 1h"))->setStatusCode(409);
	    }

		$validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ],[
                'email.required'=>1,
                'password.required'=>1,
                'email.email' =>'Votre email n\'ai pas correct',
            ]);
		if($validator->passes()){

			if (!Auth::attempt($request->only('email', 'password'))) {
				$this->incrementLoginAttempts($request);
				return response()->json(array("error"=>true, "message"=>"Votre email ou password est erroné"))->setStatusCode(401);
			}

			$this->clearLoginAttempts($request);

			$user = User::where('email', $request->email)->firstOrFail();

			$token = $user->createToken('auth_token')->plainTextToken;
			$response = array();
            $response["error"] = false;
            $response["message"] = "L'utilisateur a été authentifié avec succés";
            $response["tokens"] = array(
            					"token"=>$token,
            					"refresh-token"=>"Sayna",
            					"createdAt"=>$user->created_at,

            					);
            return response()->json($response)->setStatusCode(200);
		}
		else{
			$this->incrementLoginAttempts($request);
			$error_array = array("error"=>1);

            foreach ($validator->messages()->getMessages() as $field_name => $messages)
            {
                if($messages[0] == 1){
                    return response()->json(array("error"=>true, "message"=>"L'email/password est manquantes"))->setStatusCode(401);
                }
                else{
                    return response()->json(array("error"=>true, "message"=>$messages[0]))->setStatusCode(401);
                }
            }
		}
	}

	/**
     * Prepar la valeur d'email.
     *
     * @return \Illuminate\Http\Response
     */
	public function username()
    {
        $login = request()->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$field => $login]);
        return $field;
    }

    /**
     * Modification de l'utilisateur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  String  $tokens
     * @return \Illuminate\Http\Response
     */
    public function updates(Request $request, $tokens)
    {
    	[$id, $token] = explode('|', $tokens, 2);
    	
    	$accessToken = PersonalAccessToken::find($id);
		
		if($accessToken){
			if (!hash_equals($accessToken->token, hash('sha256', $token))) {
			    $response = [
		            'error'=>false,
		            'message'=>"Le token envoyez n'est pas conforme",
		        ];
		        return response($response)->setStatusCode(401);
			}
			elseif($this->expiration && $accessToken->created_at->lte(now()->subMinutes($this->expiration))){
				$response = [
		            'error'=>false,
		            'message'=>"Votre token n'ai' plus valide, veuillez le réinitialiser",
		        ];
		        return response($response)->setStatusCode(401);
			}
			else{
				if($request->all()){
					$userid = auth()->user()->id;
					User::find($userid)->update($request->all());
					$response = [
			            'error'=>false,
			            'message'=>"L'utilisateur a été modifiée avec succés",
			        ];
				    return response($response)->setStatusCode(200);
				}
				else{
					$response = [
			            'error'=>false,
			            'message'=>"Aucn données n'a été envoyée",
			        ];
				    return response($response)->setStatusCode(200);
				}
			}
		}else{
			$response = [
	            'error'=>false,
	            'message'=>"Le token envoyez n'existe pas",
	        ];
	        return response($response)->setStatusCode(401);
		}
    }

    /**
     * Récupérqtion de(s) utilisateur(s).
     * si $request = all => returne tous les user
     * si non return user par id
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  String  $tokens
     * @return \Illuminate\Http\Response
     */
    public function getuser(Request $request, $tokens)
    {
    	[$id, $token] = explode('|', $tokens, 2);
    	
    	$accessToken = PersonalAccessToken::find($id);
		
		if($accessToken){
			if (!hash_equals($accessToken->token, hash('sha256', $token))) {
			    $response = [
		            'error'=>false,
		            'message'=>"Le token envoyez n'est pas conforme",
		        ];
		        return response($response)->setStatusCode(401);
			}
			elseif($this->expiration && $accessToken->created_at->lte(now()->subMinutes($this->expiration))){
				$response = [
		            'error'=>false,
		            'message'=>"Votre token n'ai' plus valide, veuillez le réinitialiser",
		        ];
		        return response($response)->setStatusCode(401);
			}
			else{
				$res = array('error'=>false);
				if(!$request->all()){
					$userid = auth()->user()->id;
					$res['user'] = User::where('id', $userid)->get(['firstname', 'lastname','email','date_naissance','sexe','created_at']);
				}
				else{
					$res['users'] = User::get(['firstname', 'lastname','email','sexe']);
				}

		        return response()->json($res)->setStatusCode(200);
			}
		}else{
			$response = [
	            'error'=>false,
	            'message'=>"Le token envoyez n'existe pas",
	        ];
	        return response($response)->setStatusCode(401);
		}
    }

    /**
     * Déconnections de l'utilisateur.
     *
     * @param  String  $tokens
     * @return \Illuminate\Http\Response
     */
    public function logout($tokens){
    	[$id, $token] = explode('|', $tokens, 2);
    	$accessToken = PersonalAccessToken::find($id);
		
		if($accessToken){
			if (!hash_equals($accessToken->token, hash('sha256', $token))) {
			    $response = [
		            'error'=>false,
		            'message'=>"Le token envoyez n'est pas conforme",
		        ];
		        return response($response)->setStatusCode(401);
			}
			elseif($this->expiration && $accessToken->created_at->lte(now()->subMinutes($this->expiration))){
				$response = [
		            'error'=>false,
		            'message'=>"Votre token n'ai' plus valide, veuillez le réinitialiser",
		        ];
		        return response($response)->setStatusCode(401);
			}
			else{
				
		        auth()->user()->tokens()->where('id', $id)->delete();
			    $response = [
		            'error'=>false,
		            'message'=>"L'utilisateur a été déconecté succès",
		        ];
		        return response($response)->setStatusCode(200);
			}
		}else{
			$response = [
	            'error'=>false,
	            'message'=>"Le token envoyez n'existe pas",
	        ];
	        return response($response)->setStatusCode(401);
		}    
    }
}
