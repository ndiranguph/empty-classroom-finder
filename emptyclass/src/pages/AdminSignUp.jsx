import React from 'react';
import SignUp from './SignUp.jsx';
import googleLogo from "../assets/google.png"
import gitHubLogo from "../assets/github.png"
import { useState } from 'react';

export default function Login() {
  const[formvalue, setformvalue] = useState(
    {
      Email: '',
      Password: ''
    }
  )
  const handleInpt = (e) => {
    setdata({...setformvalue, [e.target.name]: e.target.value})
  }
  return (
    <div className="min-h-screen flex items-center justify-center bg-n-8 text-neutral-950">
      <div className="w-full max-w-md p-8 bg-white rounded-lg shadow">
        <h1 className="font-sora text-2xl font-bold mb-6 text-center text-neutral-950">
          Welcome
        </h1>
        <h2 className="font-sora text-lg mb-4 text-center text-neutral-950">
          Sign in to your account or create a new one
        </h2>
 <div className="flex justify-between  mb-6 text-base font-sora font-bold text-neutral-950 items-center border-b border-neutral-300 pb-1 space-x-2">
  {/* Login: always underlined */}
  <p className="underline underline-offset-2 decoration-3 decoration-neutral-950">
    Login
  </p>

  {/* Register: underline on hover */}
  <a
    href={SignUp}
    className="relative no-underline hover:underline hover:underline-offset-2 decoration-neutral-600 transition"
  >
    Register
  </a>
</div>

        <form className="space-y-4">
          <div>
            <label className="block mb-1 text-sm font-sora text-neutral-950" name = "Email">Email</label>
            <input
              type="email"
              required
              className="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
            />
          </div>
          <div>
            <label className="block mb-1 text-sm font-sora text-neutral-950" name = "Password">Password</label>
            <input
              type="password"
              required
              className="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
            />

          </div>
          <button
            type="submit"
            className="w-full py-2 bg-black text-white rounded hover:bg-zinc-900 font-sora"
          >
            Login
          </button>
        </form>
        <p className="mt-4 text-center text-sm font-sora p-4 text-neutral-950">
         <p className="text-sm text-gray-600">
  By continuing, you agree to our{' '}
  <span className="font-semibold">Terms of Service</span> and{' '}
  <span className="font-semibold">Privacy Policy</span>.
</p>

          <div className="flex items-center my-4">
  <div className="flex-grow border-t border-gray-300"></div>
  <p className="px-4 text-gray-500">Or continue with</p>
  <div className="flex-grow border-t border-gray-300"></div>
</div>

         <div className="flex justify-center items-center space-x-20 p-2">
      {/* Google */}
      <a href="" className="hover:bg-neutral-50 px-10 rounded-lg p-3  transition border-neutral-300 border">
        <img src={googleLogo} alt="Sign in with Google" className="w-6 h-6 object-contain" />
      </a>

      {/* GitHub */}
      <a href="" className="hover:bg-neutral-50 px-10 rounded-lg p-3 transition border border-neutral-300">
        <img src={gitHubLogo} alt="Sign in with GitHub" className="w-6 h-6 object-contain" />
      </a>
    </div>

        </p>
       
        
      </div>
    </div>
  );
}

