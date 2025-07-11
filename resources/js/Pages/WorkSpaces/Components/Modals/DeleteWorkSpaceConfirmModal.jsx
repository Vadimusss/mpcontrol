
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/Modal';
import DangerButton from '@/Components/DangerButton';
import { router } from '@inertiajs/react';

export default function DeleteWorkSpaceConfirmModal({ shopId, workSpace, maxWidth, isOpen, closeModal }) {

    const handleDelete = (e) => {
        e.preventDefault();
        router.delete(route('shops.workspaces.destroy', [shopId, workSpace.id]), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        })
    }

    return (
        <Modal maxWidth={maxWidth} show={isOpen} onClose={closeModal}>
            <div className='max-w-fit p-4'>
                <h2 className="text-lg font-medium text-gray-900">
                    Удалить рабочую область {workSpace.name}?
                </h2>
                <p className="mt-1 mb-4 text-sm text-gray-600">
                    Все настройки рабочей области будут удалены!
                </p>
                <SecondaryButton className="mr-2" onClick={closeModal}>
                    Отмена
                </SecondaryButton>

                <DangerButton onClick={(e) => handleDelete(e)}>
                    Удалить
                </DangerButton>
            </div>
        </Modal>
    );
}