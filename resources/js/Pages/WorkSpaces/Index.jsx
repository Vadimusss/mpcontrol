import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import AddWorkSpaceForm from '@/Pages/WorkSpaces/Components/Forms/AddWorkSpaceForm';
import Modal from '@/Components/Modal';
import { Head } from '@inertiajs/react';

export default function WorkSpaces({ shop, workSpaces }) {
    const [addWorkSpaceModalIsOpen, setAddWorkSpaceModalIsOpen] = useState(false);

    const handleAddWorkSpaceButtonClick = () => {
        setAddWorkSpaceModalIsOpen(true);
    };

    const closeAddWorkSpaceModal = (() => {
        setAddWorkSpaceModalIsOpen(false);
    });

    // console.log(workSpaces);

    return (
        <AuthenticatedLayout
            navigation={true}
            shopId = {shop.id}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {shop.name}
                </h2>
            }>
            <Head title={`Магазин ${shop.name}`} />

            <div className="max-w-2xl mx-auto">
                <div className="p-2 sm:p-3 lg:p-6">
                    <h2 className="text-xl font-bold mb-3">Рабочие области</h2>
                    {workSpaces && workSpaces.map((workSpace) =>
                        <p>{workSpace.name}</p>
                    )}
                    <PrimaryButton
                        className="mt-4"
                        onClick={(e) => handleAddWorkSpaceButtonClick(e)}>
                        Добавить рабочую область
                    </PrimaryButton>
                </div>
            </div>
            <Modal show={addWorkSpaceModalIsOpen} onClose={closeAddWorkSpaceModal}>
                <AddWorkSpaceForm shopId={shop.id} closeModal={() => closeAddWorkSpaceModal()} />
            </Modal>
        </AuthenticatedLayout>
    );
}
