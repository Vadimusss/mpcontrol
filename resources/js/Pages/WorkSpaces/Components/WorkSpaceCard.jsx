import { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import Modal from '@/Components/Modal';
import DeleteWorkSpaceConfirmModal from '@/Pages/WorkSpaces/Components/Modals/DeleteWorkSpaceConfirmModal';
import { usePage } from '@inertiajs/react';
import { Inertia } from '@inertiajs/inertia';

export default function WorkSpaceCard({ shopId, workSpace }) {
    const { auth } = usePage().props;
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

    return (
        <div className="border border-gray-300 rounded-md shadow-sm bg-white mb-2 p-2">
            <p>ID: {workSpace.id}</p>
            <p>{workSpace.name}</p>
            <p>Создатель: {workSpace.creator.name}</p>
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
                        <Modal show={modalState.changeSettingModalIsOpen} onClose={closeChangeSettingModal}>
                            <p>Test!!!</p>
                        </Modal>
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
                    onClick={(e) => Inertia.get(route('shops.workspaces.show', workSpace.id))}>
                    Открыть
                </PrimaryButton>
            </div>
        </div >
    );
}
