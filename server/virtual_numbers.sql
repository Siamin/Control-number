SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Table structure for table `virtual_numbers`
--

CREATE TABLE `virtual_numbers` (
  `id` int(11) NOT NULL,
  `number` varchar(15) NOT NULL,
  `status` varchar(8) NOT NULL,
  `date_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Indexes for table `virtual_numbers`
--
ALTER TABLE `virtual_numbers`
  ADD PRIMARY KEY (`id`);


--
-- AUTO_INCREMENT for table `virtual_numbers`
--
ALTER TABLE `virtual_numbers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;
COMMIT;

