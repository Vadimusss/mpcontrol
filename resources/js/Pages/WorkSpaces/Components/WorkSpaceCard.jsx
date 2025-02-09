import React, { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import DeleteWorkSpaceConfirmModal from '@/Pages/WorkSpaces/Components/Modals/DeleteWorkSpaceConfirmModal';
import SetWorkSpaceSettingModal from '@/Pages/WorkSpaces/Components/Modals/SetWorkSpaceSettingModal';
import { Inertia } from '@inertiajs/inertia';

export default function WorkSpaceCard({ auth, shopId, workSpace, goodLists }) {
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
// console.log(workSpace);
    return (
        <div className="border border-gray-300 rounded-md shadow-sm bg-white mb-2 p-2">
            <p><span className='font-semibold text-gray-900'>ID:</span> {workSpace.id}</p>
            <p><span className='font-semibold text-gray-900'>Название:</span> {workSpace.name}</p>
            <p><span className='font-semibold text-gray-900'>Создатель:</span> {workSpace.creator.name}</p>
            {workSpace.connected_good_lists.length !== 0 &&
                <>
                    <p><span className='font-semibold text-gray-900'>Подключенные списки:</span>
                    {workSpace.connected_good_lists.map((list) => <React.Fragment key={list.id}> {list.name}</React.Fragment> )}
                    </p>
                </>
            }
            <div className="flex flex-col">
                {(workSpace.creator.id === auth.user.id) &&
                    <>
                        <div className="flex gap-x-2">
                            <PrimaryButton
                                className="mt-4 max-w-fit"
                                onClick={(e) => openChangeSettingModal(e)}>
                                Настройки
                            </PrimaryButton>
                            <PrimaryButton
                                className="mt-4 max-w-fit"
                                onClick={(e) => openDeleteModal()}>
                                Удалить
                            </PrimaryButton>
                        </div>
                        <SetWorkSpaceSettingModal
                            shopId={shopId}
                            workSpace={workSpace}
                            goodLists={goodLists}
                            maxWidth={'xl'}
                            IsOpen={modalState.changeSettingModalIsOpen}
                            closeModal={closeChangeSettingModal}
                        />
                        <DeleteWorkSpaceConfirmModal
                            shopId={shopId}
                            workSpace={workSpace}
                            maxWidth={'xl'}
                            IsOpen={modalState.deleteConfirmModalIsOpen}
                            closeModal={closeDeleteModal} />
                    </>
                }
                <PrimaryButton
                    className="mt-4 max-w-fit"
                    onClick={(e) => Inertia.get(route('shops.workspaces.show', { shop: shopId, workspace: workSpace.id }))}>
                    Открыть
                </PrimaryButton>
            </div>
        </div >
    );
}
