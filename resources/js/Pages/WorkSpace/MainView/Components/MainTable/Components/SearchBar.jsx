import React, { useState, useEffect, useRef } from 'react';
import { observer } from 'mobx-react-lite';
import { viewStore } from '../../../Stores/ViewStore';
import { MagnifyingGlassIcon, XMarkIcon } from '@heroicons/react/24/outline';
import '../../../styles.css';

export const SearchBar = observer(({ onClose }) => {
  const [localQuery, setLocalQuery] = useState(viewStore.searchQuery);
  const inputRef = useRef(null);
  
  useEffect(() => {
    const timer = setTimeout(() => {
      if (localQuery !== viewStore.searchQuery) {
        viewStore.setSearchQuery(localQuery);
      }
    }, 300);
    
    return () => clearTimeout(timer);
  }, [localQuery]);
  
  useEffect(() => {
    if (inputRef.current) {
      inputRef.current.focus();
      inputRef.current.select();
    }
  }, []);
  
  const handleClear = () => {
    setLocalQuery('');
    viewStore.clearSearch();
  };
  
  const handleInputChange = (e) => {
    setLocalQuery(e.target.value);
  };
  
  const totalResults = viewStore.searchResults.length;
  
  return (
    <div className="search-bar">
      <div className="search-bar-content">
        <div className="search-input-container">
          <MagnifyingGlassIcon className="search-icon" />
          <input
            ref={inputRef}
            type="text"
            value={localQuery}
            onChange={handleInputChange}
            placeholder="Арт., Название, Вариант, Арт. WB"
            className="search-input"
          />
          {localQuery && (
            <button
              onClick={handleClear}
              className="clear-button"
              title="Очистить поиск"
            >
              <XMarkIcon className="clear-icon" />
            </button>
          )}
        </div>
        
        {totalResults > 0 && (
          <div className="search-counter">
            Найдено: {totalResults}
          </div>
        )}
        
        <button
          onClick={onClose}
          className="close-button"
          title="Закрыть поиск (Esc)"
        >
          <XMarkIcon className="close-icon" />
        </button>
      </div>
    </div>
  );
});
