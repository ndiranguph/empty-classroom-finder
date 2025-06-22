import React from 'react';
import googleLogo from "../assets/google.png";
import gitHubLogo from "../assets/github.png";
import { Link } from 'react-router-dom';
import { useState } from 'react';
import axios from 'axios';

axios.defaults.baseURL = '/api'
axios.defaults.withCredentials = true


export default function SignUp() {

  const [formValue, setFormValue] = useState({
    firstname: '',
    lastname: '',
    email: '',
    password: ''
  })
  
  const handleInputChange = (e) => {
    setFormValue({ ...formValue, [e.target.name]: e.target.value });
 
  }
  const handleSubmit = async (e) => {
    e.preventDefault();
    const forData = {
      firstname: formValue.firstname,
      lastname: formValue.lastname,
      email: formValue.email,
      password: formValue.password
    };
   try {
  const res = await axios.post("/register.php", forData);
  console.log(res.data);
} catch (err) {
  console.error(err.response?.data || err);
}


  }
  return (
    <div className="min-h-screen flex items-center justify-center bg-n-8 text-neutral-950">
      <div className="w-full max-w-md p-8 bg-white rounded-lg shadow">
        <h1 className="font-sora text-2xl font-bold mb-6 text-center text-neutral-950">
          Create Account
        </h1>
        <h2 className="font-sora text-lg mb-4 text-center text-neutral-950">
          Register for a new account or log in
        </h2>

        <div className="flex justify-between mb-6 text-base font-sora font-bold text-neutral-950 items-center border-b border-neutral-300 pb-1 space-x-2">
          <Link to="/login"> 
          <a
            className="relative no-underline hover:underline hover:underline-offset-2 decoration-neutral-600 transition"
          >
            Login
          </a>
          </Link>

          <p className="underline underline-offset-2 decoration-3 decoration-neutral-950" >
            Register
          </p>
        </div>

        <form className="space-y-4" onSubmit={handleSubmit}> 
          <div>
            <label className="block mb-1 text-sm font-sora text-neutral-950"  >Firstname</label>
            <input
              type="text"
              required
              className="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
              value ={formValue.firstname}
              onChange={handleInputChange}
              name="firstname"
            />
            <label className="block mb-1 text-sm font-sora text-neutral-950"  >Lastname</label>
            <input
              type="text"
              required
              className="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
              value ={formValue.lastname}
              onChange={handleInputChange}
              name="lastname"
            />

          </div>
          <div>
            <label className="block mb-1 text-sm font-sora text-neutral-950" >Email</label>
            <input
              type="email"
              required
              className="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
              value={formValue.email}
              onChange={handleInputChange}
              name="email"
            />
          </div>
          <div>
            <label className="block mb-1 text-sm font-sora text-neutral-950" >Password</label>
            <input
              type="password"
              required
              className="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
              value={formValue.eassword}
              onChange={handleInputChange}
              name="password"
            />
          </div>
          

          <button
            type="submit"
            className="w-full py-2 bg-black text-white rounded hover:bg-zinc-900 font-sora"
          >
            Register
          </button>
        </form>

        <p className="mt-4 text-center text-sm font-sora p-4 text-neutral-950">
          <span className="text-sm text-gray-600">
            By continuing, you agree to our{' '}
            <span className="font-semibold">Terms of Service</span> and{' '}
            <span className="font-semibold">Privacy Policy</span>.
          </span>
        </p>

        <div className="flex items-center my-4">
          <div className="flex-grow border-t border-gray-300"></div>
          <p className="px-4 text-gray-500">Or continue with</p>
          <div className="flex-grow border-t border-gray-300"></div>
        </div>

        <div className="flex justify-center items-center space-x-20 p-2">
          <a href="" className="hover:bg-neutral-50 px-10 rounded-lg p-3 transition border border-neutral-300">
            <img src={googleLogo} alt="Sign up with Google" className="w-6 h-6 object-contain" />
          </a>
          <a href="" className="hover:bg-neutral-50 px-10 rounded-lg p-3 transition border border-neutral-300">
            <img src={gitHubLogo} alt="Sign up with GitHub" className="w-6 h-6 object-contain" />
          </a>
        </div>
      </div>
    </div>
  );
}
