import React, { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import DeleteWorkSpaceConfirmModal from '@/Pages/WorkSpaces/Components/Modals/DeleteWorkSpaceConfirmModal';
import SetWorkSpaceSettingModal from '@/Pages/WorkSpaces/Components/Modals/SetWorkSpaceSettingModal';
import { router } from '@inertiajs/react';

export default function WorkSpaceCard({ auth, shopId, workSpace, goodLists, views }) {
    const [modalState, setModalIState] = useState({
        changeSettingModalIsOpen: false,
        deleteConfirmModalIsOpen: false,
    });

    const openChangeSettingModal = (() => {
        setModalIState({ changeSettingModalIsOpen: true });
    });

    const closeChangeSettingModal = (() => {
        setModalIState({ changeSettingModalIsOpen: false });
    });

    const openDeleteModal = (() => {
        setModalIState({ deleteConfirmModalIsOpen: true });
    });

    const closeDeleteModal = (() => {
        setModalIState({ deleteConfirmModalIsOpen: false });
    });

    const isGoodListsExists = goodLists.length !== 0;
    const isGoodListsConnected = workSpace.connected_good_lists.length !== 0;

    return (
        <div className="border border-gray-300 rounded-md shadow-sm bg-white mb-2 p-2">
            <p><span className='font-semibold text-gray-900'>Название:</span> {workSpace.name}</p>
            <p><span className='font-semibold text-gray-900'>Создатель:</span> {workSpace.creator.name}</p>
            {workSpace.connected_good_lists.length !== 0 &&
                <>
                    <p><span className='font-semibold text-gray-900'>Подключенные списки:</span>
                        {workSpace.connected_good_lists.map((list) => <React.Fragment key={list.id}> {list.name}</React.Fragment>)}
                    </p>
                </>
            }
            <p><span className='font-semibold text-gray-900'>Представление:</span> {workSpace.view_settings.view.name}</p>
            <div className="flex flex-col">
                {(workSpace.creator.id === auth.user.id) &&
                    <>
                        <div className="flex gap-x-2">
                            <PrimaryButton
                                className="mt-4 max-w-fit"
                                onClick={(e) => openChangeSettingModal(e)}
                                disabled={!isGoodListsExists}>
                                Настройки
                            </PrimaryButton>
                            <PrimaryButton
                                className="mt-4 max-w-fit"
                                onClick={(e) => openDeleteModal()}>
                                Удалить
                            </PrimaryButton>
                        </div>
                        {isGoodListsExists && <SetWorkSpaceSettingModal
                            shopId={shopId}
                            workSpace={workSpace}
                            goodLists={goodLists}
                            views={views}
                            maxWidth={'xl'}
                            isOpen={modalState.changeSettingModalIsOpen}
                            closeModal={closeChangeSettingModal}
                        />}
                        <DeleteWorkSpaceConfirmModal
                            shopId={shopId}
                            workSpace={workSpace}
                            maxWidth={'xl'}
                            isOpen={modalState.deleteConfirmModalIsOpen}
                            closeModal={closeDeleteModal} />
                    </>
                }
                <PrimaryButton
                    className="mt-4 max-w-fit"
                    disabled={!isGoodListsExists || !isGoodListsConnected}
                    onClick={(e) => router.get(route('shops.workspaces.show', { shop: shopId, workspace: workSpace.id }))}>
                    Открыть
                </PrimaryButton>
            </div>
        </div >
    );
}
