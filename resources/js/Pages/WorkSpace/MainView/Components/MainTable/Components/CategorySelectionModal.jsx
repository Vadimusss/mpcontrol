import React, { useMemo, useState, useEffect } from 'react';
import { observer } from 'mobx-react-lite';
import viewStore from '../../../Stores/ViewStore';
import { XMarkIcon, CheckIcon } from '@heroicons/react/24/outline';
import '../../../styles.css';

export const CategorySelectionModal = observer(({ categoriesData = {} }) => {
    const categoryNames = useMemo(() => {
        return Object.keys(categoriesData);
    }, [categoriesData]);

    const [localSelected, setLocalSelected] = useState([]);
    const [hasChanges, setHasChanges] = useState(false);

    useEffect(() => {
        if (viewStore.showCategorySelectionModal) {
            if (viewStore.selectedCategories.length === 0) {
                setLocalSelected([...categoryNames]);
            } else {
                setLocalSelected([...viewStore.selectedCategories]);
            }
            setHasChanges(false);
        }
    }, [viewStore.showCategorySelectionModal, categoryNames, viewStore.selectedCategories]);

    const handleClose = (e) => {
        e.preventDefault();
        e.stopPropagation();
        viewStore.toggleCategorySelectionModal();
    };

    const handleBackdropClick = (e) => {
        if (e.target === e.currentTarget) {
            handleClose(e);
        }
    };

    const handleToggleCategory = (categoryName) => {
        const newSelected = localSelected.includes(categoryName)
            ? localSelected.filter(cat => cat !== categoryName)
            : [...localSelected, categoryName];
        setLocalSelected(newSelected);
        setHasChanges(true);
    };

    const handleApply = () => {
        viewStore.selectedCategories = [...localSelected];
        viewStore.debouncedSaveState();
        viewStore.toggleCategorySelectionModal();
    };

    const isCategorySelected = (categoryName) => {
        return localSelected.includes(categoryName);
    };

    if (categoryNames.length === 0) return null;

    return (
        <div 
            className="category-selection-modal-overlay"
            onClick={handleBackdropClick}
        >
            <div className="category-selection-modal">
                <div className="category-selection-modal-header">
                    <h3 className="category-selection-modal-title">
                        Выбор категорий для отображения
                    </h3>
                    <button 
                        className="category-selection-modal-close"
                        onClick={handleClose}
                        title="Закрыть"
                    >
                        <XMarkIcon className="category-selection-modal-close-icon" />
                    </button>
                </div>
                
                <div className="category-selection-modal-content">
                    <div className="category-selection-list">
                        {categoryNames.map(categoryName => {
                            const selected = isCategorySelected(categoryName);
                            
                            return (
                                <div 
                                    key={categoryName}
                                    className="category-selection-item"
                                >
                                    <label className="category-selection-label">
                                        <input
                                            type="checkbox"
                                            checked={selected}
                                            onChange={() => handleToggleCategory(categoryName)}
                                            className="category-selection-checkbox"
                                        />
                                        <span className="category-selection-text">
                                            {categoryName}
                                        </span>
                                    </label>
                                </div>
                            );
                        })}
                    </div>
                    <div className="category-selection-footer">
                        <button
                            className="category-selection-apply-btn"
                            onClick={handleApply}
                            disabled={!hasChanges}
                        >
                            <CheckIcon className="category-selection-apply-icon" />
                            Применить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
});
