
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/Modal';
import DangerButton from '@/Components/DangerButton';
import { router } from '@inertiajs/react';

export default function DeleteGoodListConfirmModal({ shopId, goodList, maxWidth, IsOpen, closeModal }) {

    const handleDelete = (e) => {
        e.preventDefault();
        router.delete(route('shops.goodlists.destroy', [shopId, goodList.id]), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        })
    }

    return (
        <Modal maxWidth={maxWidth} show={IsOpen} onClose={closeModal}>
            <div className='max-w-fit p-4'>
                <h2 className="text-lg font-medium text-gray-900">
                    Удалить список товаров {goodList.name}?
                </h2>
                <p className="mt-1 mb-4 text-sm text-gray-600">
                    Если товар есть только в данном списке он будет полностью удален из базы вместе со всеми связанными данными!
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