import Button from './components/Button';
import ButtonGradient from "./assets/svg/ButtonGradient"
import Login from './pages/Login.jsx';
import SignUp from './pages/SignUp.jsx';
import { Link } from 'react-router-dom';

function App() {

  return (
    <>
    <div className="font-sans text-gray-800">
      {/* Header */}
      <header className="flex justify-between items-center px-6 py-4 bg-white shadow">
        <div className="text-xl font-bold">ClassroomFinder</div>
        <div>
        <Link to = "/login" ><button className="mr-4 text-sm font-medium" >Login</button></Link>
         <Link to = "/SignUp"> <button className="bg-black text-white px-3 py-1 rounded text-sm font-medium" >
            Register
          </button>
          </Link>
        </div>
      </header>

      {/* Hero Section */}
      <section className="text-center py-20 px-4 bg-gray-50">
        <h1 className="text-4xl font-bold mb-4">Find Available Classrooms Instantly</h1>
        <p className="max-w-xl mx-auto text-gray-600 mb-8">
          Real-time classroom availability tracking for students and administrators.
          Book spaces efficiently and manage campus resources with ease.
        </p>
        <button className="bg-black text-white px-6 py-2 rounded font-medium">
          Find Available Classrooms
        </button>
      </section>

      {/* Key Features */}
      <section className="py-16 px-6 bg-white text-center">
        <h2 className="text-2xl font-semibold mb-12">Key Features</h2>
        <div className="flex flex-col md:flex-row justify-center gap-12">
          <div className="flex-1">
            <div className="text-3xl mb-2">🕒</div>
            <h3 className="font-bold mb-1">Real-time Availability</h3>
            <p className="text-gray-600">
              Check classroom availability in real-time with live updates and accurate scheduling.
            </p>
          </div>
          <div className="flex-1">
            <div className="text-3xl mb-2">🛡️</div>
            <h3 className="font-bold mb-1">Secure Login</h3>
            <p className="text-gray-600">
              Protected access with role-based authentication for students and administrators.
            </p>
          </div>
          <div className="flex-1">
            <div className="text-3xl mb-2">👤</div>
            <h3 className="font-bold mb-1">Easy to Use</h3>
            <p className="text-gray-600">
              Intuitive interface designed for quick classroom searches and bookings.
            </p>
          </div>
        </div>
      </section>

      {/* Call to Action */}
      <section className="py-16 px-6 bg-gray-50 text-center">
        <h2 className="text-2xl font-semibold mb-4">Ready to Get Started?</h2>
        <p className="text-gray-600 mb-6">
          Join thousands of students and faculty using our platform daily.
        </p>
        <div>
          <Link to="/SignUp">
          <button className="bg-black text-white px-5 py-2 rounded mr-3 font-medium">
            Create Account
          </button>
          </Link>
          <button className="border border-gray-400 px-5 py-2 rounded font-medium">
            Learn More
          </button>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-black text-white py-10 px-6 text-sm">
        <div className="flex flex-col md:flex-row justify-between max-w-6xl mx-auto">
          <div className="mb-6 md:mb-0">
            <h3 className="font-bold mb-2">ClassroomFinder</h3>
            <p>Efficient classroom management for modern educational institutions.</p>
          </div>
          <div className="mb-6 md:mb-0">
            <h4 className="font-bold mb-2">Product</h4>
            <ul>
              <li>Features</li>
              <li>Pricing</li>
              <li>Support</li>
            </ul>
          </div>
          <div className="mb-6 md:mb-0">
            <h4 className="font-bold mb-2">Company</h4>
            <ul>
              <li>About</li>
              <li>Contact</li>
              <li>Privacy</li>
            </ul>
          </div>
          <div>
            <h4 className="font-bold mb-2">Connect</h4>
            <div className="flex gap-4">
              <span>🐦</span>
              <span>📘</span>
              <span>📸</span>
              <span>🔗</span>
            </div>
          </div>
        </div>
      </footer>
    </div>
    </>
  )
}

export default App
