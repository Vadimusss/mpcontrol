import React, { useEffect, useCallback } from 'react';
import { observer } from 'mobx-react-lite';
import { XMarkIcon } from '@heroicons/react/24/outline';
import { GoodDetailsTable } from '../Components/GoodDetailsTable';
import NotesModal from './NotesModal';
import notesStore from '../Stores/NotesStore';
import viewStore from '../Stores/ViewStore';
import { goodsStore } from '../Stores/GoodsStore';
import '../styles.css';

export const GoodDetailsModal = observer(({
    isOpen,
    onClose,
    good,
    shop,
    dates,
    workSpaceSettings
}) => {

    const { viewId } = viewStore;

    useEffect(() => {
        if (isOpen && good) {
            goodsStore.loadGoodDetails(shop.id, good.id, dates);
        } else {
            goodsStore.clearGoodDetails();
            notesStore.setRefreshCallback(null);
        }
    }, [isOpen, good, shop.id, dates]);

    const handleOpenNotes = useCallback((date, goodId) => {
        notesStore.openModal({ date, goodId, viewId });
        notesStore.setRefreshCallback(() => {
            goodsStore.updateNoteExists(date, goodId, viewId);
        });
    }, [viewId]);

    if (!isOpen) return null;

    return (
        <>
            <div className="good-details-modal-overlay">
                <div className="good-details-modal-container">
                    <div className="good-details-modal-backdrop" onClick={onClose}></div>
                    <div className="good-details-modal">
                        <div className="good-details-modal-header">
                            <div className="good-details-modal-header-content">
                                <div className="good-details-modal-header-left">
                                    <div className="good-details-modal-title">
                                        <div>
                                            <p><b>{good?.article} | {good?.name} | {good?.variant} |&nbsp;
                                                <a
                                                    href={`https://www.wildberries.ru/catalog/${good?.wbArticle}/detail.aspx`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >{good?.wbArticle}</a></b></p>
                                        </div>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    className="good-details-modal-close"
                                    onClick={onClose}
                                >
                                    <XMarkIcon className="good-details-modal-close-icon" />
                                </button>
                            </div>
                        </div>

                        <div className="good-details-modal-content">
                            {goodsStore.isLoadingGoodDetails ? (
                                <div className="good-details-modal-loading">
                                    <div className="good-details-modal-spinner"></div>
                                    <span className="good-details-modal-loading-text">Загрузка данных...</span>
                                </div>
                            ) : goodsStore.goodDetails ? (
                                <GoodDetailsTable
                                    goodDetails={goodsStore.goodDetails}
                                    dates={dates}
                                    workSpaceSettings={workSpaceSettings}
                                    handleOpenNotes={handleOpenNotes}
                                />
                            ) : (
                                <div className="good-details-modal-error">
                                    Не удалось загрузить данные
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <NotesModal />
        </>
    );
});
