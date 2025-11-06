import { useState, useEffect } from 'react';
import axios from 'axios';

export function CurrencySelector() {
  const [currencies, setCurrencies] = useState([]);
  const [selected, setSelected] = useState('USD');

  useEffect(() => {
    const fetchCurrencies = async () => {
      try {
        const response = await axios.get('/api/currencies');
        setCurrencies(response.data);
      } catch (error) {
        console.error('Error fetching currencies:', error);
      }
    };

    fetchCurrencies();
    
    const saved = localStorage.getItem('currency');
    if (saved) setSelected(saved);
  }, []);

  const handleChange = (code: string) => {
    setSelected(code);
    localStorage.setItem('currency', code);
    window.location.reload();
  };

  return (
    <select value={selected} onChange={(e) => handleChange(e.target.value)} aria-label="Select currency">
      {currencies.map((curr: any) => (
        <option key={curr.code} value={curr.code}>
          {curr.symbol} {curr.code}
        </option>
      ))}
    </select>
  );
}
