import React, { useState } from 'react'
import { FaWifi, FaDesktop, FaUtensils } from 'react-icons/fa'
import { AiOutlineSearch } from 'react-icons/ai'

export default function StudentPortal() {
  const [date, setDate] = useState('')
  const [startTime, setStartTime] = useState('')
  const [endTime, setEndTime] = useState('')
  const [filterBuilding, setFilterBuilding] = useState('All')
  const [sortBy, setSortBy] = useState('Room Name')

  const rooms = [
    { name: 'Room 101', building: 'Engineering Hall', capacity: 25, start: '09:00 AM', end: '11:00 AM' },
    { name: 'Room 205', building: 'Science Center', capacity: 15, start: '09:00 AM', end: '11:00 AM'},
    { name: 'Conference A', building: 'Library Building', capacity: 12, start: '09:00 AM', end: '11:00 AM' },
    { name: 'Study Hall 3', building: 'Student Center', capacity: 8, start: '09:00 AM', end: '11:00 AM'},
    { name: 'Lab 301', building: 'Engineering Hall', capacity: 20, start: '09:00 AM', end: '11:00 AM'},
    { name: 'Seminar Room B', building: 'Library Building', capacity: 30, start: '09:00 AM', end: '11:00 AM' }
  ]

  return (
    <div className="p-8 space-y-6">
      <header className="flex justify-between items-center">
        <h1 className="text-xl font-bold"><span className="inline-block align-middle mr-2">🎓</span>Student Portal</h1>
        <div>Welcome, Student <img src="/path/to/avatar.png" alt="avatar" className="inline h-8 w-8 rounded-full ml-2" /></div>
      </header>

      <section className="bg-white p-4 rounded-lg shadow space-y-4">
        <form className="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div>
            <label>Date</label>
            <input type="date" value={date} onChange={e => setDate(e.target.value)} className="w-full border rounded p-2" />
          </div>
          <div>
            <label>Start Time</label>
            <input type="time" value={startTime} onChange={e => setStartTime(e.target.value)} className="w-full border rounded p-2" />
          </div>
          <div>
            <label>End Time</label>
            <input type="time" value={endTime} onChange={e => setEndTime(e.target.value)} className="w-full border rounded p-2" />
          </div>
          <button type="submit" className="bg-blue-900 text-white p-2 rounded flex items-center justify-center"><AiOutlineSearch className="mr-1" />Search</button>
        </form>

        <div className="flex items-center space-x-4">
          <select className="border rounded p-2" value={filterBuilding} onChange={e => setFilterBuilding(e.target.value)}>
            <option>All Buildings</option>
            <option>Engineering Hall</option>
            <option>Science Center</option>
            <option>Library Building</option>
            <option>Student Center</option>
          </select>
         
          <select className="border rounded p-2" value={sortBy} onChange={e => setSortBy(e.target.value)}>
            <option>Room Name</option>
          </select>
          <div className="ml-auto">24 rooms found</div>
        </div>
      </section>

      <section className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {rooms.map(r => (
          <div key={r.name} className="bg-white p-4 rounded-lg shadow">
            <div className="flex justify-between items-center mb-2">
              <h3 className="text-lg font-semibold">{r.name}</h3>
              <span className="px-2 py-1 bg-green-100 text-green-800 text-sm rounded">Available</span>
            </div>
            <div className="text-gray-600 mb-2">{r.building}</div>
            <div className="text-gray-600 mb-2">Capacity: {r.capacity}</div>
            <div className="text-gray-600 mb-4">{r.start} – {r.end}</div>
            <div className="flex space-x-2">
              {r.features.map(f => featureIcon(f))}
            </div>
          </div>
        ))}
      </section>

     
    </div>
)
}
